<?php

namespace App\Modules\WhatsApp\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WhatsApp\Models\Message;
use App\Modules\WhatsApp\Services\WhatsAppService;
use App\Modules\Customers\Models\Customer;
use App\Modules\Companies\Models\Company;
use App\Modules\Queue\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Webhook Verification (Meta requirement).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');

        if (
            $request->input('hub_mode') === 'subscribe' &&
            $request->input('hub_verify_token') === $verifyToken
        ) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp messages.
     */
    public function handle(Request $request)
    {
        // Verify Webhook Signature if WHATSAPP_APP_SECRET is configured
        $appSecret = config('services.whatsapp.app_secret');
        if ($appSecret) {
            $signatureHeader = $request->header('X-Hub-Signature-256');
            if (!$signatureHeader) {
                return response('Missing signature header', 401);
            }

            $signatureParts = explode('=', $signatureHeader, 2);
            if (count($signatureParts) !== 2 || $signatureParts[0] !== 'sha256') {
                return response('Invalid signature header format', 401);
            }

            $expectedSignature = hash_hmac('sha256', $request->getContent(), $appSecret);
            if (!hash_equals($expectedSignature, $signatureParts[1])) {
                return response('Signature mismatch', 401);
            }
        }

        Log::info('WhatsApp Webhook Payload received');

        $entry = $request->input('entry.0.changes.0.value');

        if (!isset($entry['messages'][0])) {
            return response('No messages found', 200);
        }

        $msgData = $entry['messages'][0];
        $phone = Customer::normalizePhone($msgData['from']);
        $text = trim($msgData['text']['body'] ?? '');

        if (!$text) {
            return response('Empty message', 200);
        }

        // 1. Normalize phone
        $normalizedPhone = $this->whatsapp->normalizePhone($phone);

        // 2. Find the most recently active Customer record for this phone
        $customer = Customer::where('phone', $normalizedPhone)
            ->orderBy('last_user_message_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($customer) {
            // Update last_user_message_at everywhere so state is synced across their company profiles
            Customer::where('phone', $normalizedPhone)->update(['last_user_message_at' => now()]);
        }

        // 3. Save Inbound Message
        $inboundMsg = Message::create([
            'phone' => $normalizedPhone,
            'message' => $text,
            'direction' => 'inbound',
            'company_id' => $customer ? $customer->last_company_id : null,
            'created_at' => now(),
        ]);

        // 4. Logic Cases
        // CASE 1: Message contains Company Code
        // Extract the code if it's part of the standard tracking message
        $possibleCode = $text;
        if (preg_match('/:\s*([A-Za-z0-9]+)$/', $text, $matches)) {
            $possibleCode = $matches[1];
        } else {
            // Also check the last word just in case
            $words = explode(' ', $text);
            $possibleCode = preg_replace('/[^a-zA-Z0-9]/', '', end($words));
        }

        $company = Company::where('code', $text)->orWhere('code', $possibleCode)->first();
        if ($company) {
            // Update the inbound message to belong to the new company it is addressing
            $inboundMsg->update(['company_id' => $company->id]);
            
            // Check if WhatsApp tracking is enabled for this company
            if (!$company->hasFeature('whatsapp.send')) {
                $this->whatsapp->sendMessage(
                    $normalizedPhone,
                    "WhatsApp service is unavailable for this company. Please use other available tracking methods.",
                    null
                );
                return response('WhatsApp not enabled for this company', 200);
            }
            return $this->handleCompanyRegistration($company, $normalizedPhone, $customer);
        }
        Log::info("CASE 2:", [$request->user()]);
        // CASE 2: Message = "STATUS"
        if (strtoupper($text) === 'STATUS') {
            return $this->handleStatusCheck($normalizedPhone, $customer);
        }

        // Fallback or automated reply for unknown commands
        Log::info("WhatsApp: Unknown command '{$text}' from {$normalizedPhone}");

        return response('OK', 200);
    }

    /**
     * Handle company code registration.
     */
    protected function handleCompanyRegistration(Company $company, string $phone, ?Customer $customer)
    {
        // Find customer globally or in this specific company
        $companyCustomer = Customer::where('phone', $phone)
            ->where(function($q) use ($company) {
                $q->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$companyCustomer) {
            Log::info("WhatsApp: No customer found for {$phone} at {$company->name}");
            $this->whatsapp->sendMessage($phone, "You are not registered in {$company->name}. Please register at the counter first.", $company->id);
            return response('Not Registered', 200);
        }

        Log::info("companyCustomer found:", ['id' => $companyCustomer->id]);

        // Save last_company_id across all profiles for this phone so STATUS works everywhere
        Customer::where('phone', $phone)->update([
            'last_company_id' => $company->id,
            'last_user_message_at' => now()
        ]);

        // Get Active Ticket
        $ticket = Ticket::where('customer_id', $companyCustomer->id)
            ->where('company_id', $company->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$ticket) {
            $this->whatsapp->sendMessage($phone, "No active ticket found for you at {$company->name}.", $company->id);
            return response('No Ticket', 200);
        }

        return $this->replyWithTicketInfo($phone, $ticket, $company);
    }

    /**
     * Handle STATUS command.
     */
    protected function handleStatusCheck(string $phone, ?Customer $customer)
    {
        if (!$customer || !$customer->last_company_id) {
            $this->whatsapp->sendMessage($phone, "Please send your company code first to link your account.");
            return response('No Company Linked', 200);
        }

        $company = Company::find($customer->last_company_id);
        if (!$company) {
            $this->whatsapp->sendMessage($phone, "Linked company not found. Please resend the company code.");
            return response('Company Not Found', 200);
        }

        // Find the specific customer record for that company (or global)
        $companyCustomer = Customer::where('phone', $phone)
            ->where(function($q) use ($company) {
                $q->where('company_id', $company->id)->orWhereNull('company_id');
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$companyCustomer) {
            $this->whatsapp->sendMessage($phone, "You are not registered in {$company->name}.");
            return response('Not Registered', 200);
        }

        $ticket = Ticket::where('customer_id', $companyCustomer->id)
            ->where('company_id', $company->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$ticket) {
            $this->whatsapp->sendMessage($phone, "No active ticket found.");
            return response('No Ticket', 200);
        }

        return $this->replyWithTicketInfo($phone, $ticket, $company);
    }

    /**
     * Reply with ticket info.
     */
    protected function replyWithTicketInfo(string $phone, Ticket $ticket, Company $company)
    {
        $peopleAhead = Ticket::where('company_id', $company->id)
            ->where('status', 'waiting')
            ->where('position', '<', $ticket->position)
            ->where('room_id', $ticket->room_id)
            ->count();

        Log::info("peopleAhead:", [$peopleAhead]);

        $message = "👤 Name: {$ticket->customer->full_name}\n";
        $message .= "🎫 Ticket: {$ticket->ticket_number}\n";
        $message .= "🏢 Company: {$company->name}\n";
        $message .= "⏳ People ahead: {$peopleAhead}";

        if ($ticket->status === 'serving') {
            $message .= "\n✅ You are currently being served!";
        } elseif ($ticket->status === 'called') {
            $message .= "\n📢 It is your turn! Please proceed to the room.";
        }

        $this->whatsapp->sendMessage($phone, $message, $company->id);

        return response('OK', 200);
    }
}
