<?php

namespace App\Modules\WhatsApp\Services;

use App\Modules\WhatsApp\Models\Message;
use App\Modules\Customers\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppService
{
    protected string $token;
    protected string $phoneId;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.phone_id');
        $this->apiUrl = "https://graph.facebook.com/v25.0/{$this->phoneId}/messages";
    }

    /**
     * Send a WhatsApp message.
     * 
     * @param string $to Normalized phone number
     * @param string $text Message content
     * @param int|null $companyId For tracking and scoping
     * @return bool
     */
    public function sendMessage(string $to, string $text, ?int $companyId = null): bool
    {
        // 1. Normalize phone
        $to = $this->normalizePhone($to);
        // 2. 24h Protection Logic
        Log::info("Protection Logic:", [$to]);
        if (!$this->canSendTo($to)) {
            Log::warning("WhatsApp: Cannot send message to {$to}. 24h window expired or no inbound message from user.");
            return false;
        }

        // 3. Send via Meta API
        try {
            Log::info("Sending message to {$to}");
            $response = Http::timeout(10)->withToken($this->token)->withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $to,
                        'type' => 'text',
                        'text' => [
                            'body' => $text
                        ]
                    ]);

            if ($response->successful()) {
                // 4. Save outbound message
                Log::info("Message saved to database");
                Message::create([
                    'phone' => $to,
                    'message' => $text,
                    'direction' => 'outbound',
                    'company_id' => $companyId,
                    'created_at' => now(),
                ]);

                return true;
            }

            Log::error("WhatsApp API Error: " . $response->body());
        } catch (\Exception $e) {
            Log::error("WhatsApp Service Exception: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Normalize phone number to format: 212XXXXXXXXX
     */
    public function normalizePhone(string $phone): string
    {
        return Customer::normalizePhone($phone) ?? '';
    }

    /**
     * Check if we can reply to this user (24h window).
     */
    public function canSendTo(string $phone): bool
    {
        $customer = Customer::where('phone', $phone)->first();

        if (!$customer || !$customer->last_user_message_at) {
            return false;
        }

        $lastMessageAt = Carbon::parse($customer->last_user_message_at);
        return $lastMessageAt->diffInHours(now()) < 24;
    }
}
