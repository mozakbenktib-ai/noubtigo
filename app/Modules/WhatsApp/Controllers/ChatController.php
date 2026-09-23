<?php

namespace App\Modules\WhatsApp\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WhatsApp\Models\Message;
use App\Modules\WhatsApp\Services\WhatsAppService;
use App\Modules\Customers\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Display a list of conversations.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;

        // Get unique phones and their last message
        $conversations = Message::where('company_id', $companyId)
            ->select('phone', DB::raw('MAX(created_at) as last_activity'))
            ->groupBy('phone')
            ->orderBy('last_activity', 'desc')
            ->get();

        foreach ($conversations as $conv) {
            $conv->last_activity = \Carbon\Carbon::parse($conv->last_activity);

            $conv->last_message = Message::withoutGlobalScope(\App\Modules\Core\Scopes\TenantScope::class)
                ->where('phone', $conv->phone)
                ->where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->first();

            $conv->customer = Customer::where('phone', $conv->phone)
                ->where('company_id', $companyId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return view('modules.whatsapp.index', compact('conversations'));
    }

    /**
     * Display chat history for a specific phone.
     */
    public function show($phone)
    {
        $companyId = auth()->user()->company_id;
        $phone = $this->whatsapp->normalizePhone($phone);

        // Get unique phones for sidebar
        $conversations = Message::where('company_id', $companyId)
            ->select('phone', DB::raw('MAX(created_at) as last_activity'))
            ->groupBy('phone')
            ->orderBy('last_activity', 'desc')
            ->get();

        foreach ($conversations as $conv) {
            $conv->last_activity = \Carbon\Carbon::parse($conv->last_activity);

            $conv->last_message = Message::withoutGlobalScope(\App\Modules\Core\Scopes\TenantScope::class)
                ->where('phone', $conv->phone)
                ->where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->first();

            $conv->customer = Customer::where('phone', $conv->phone)
                ->where('company_id', $companyId)
                ->orderBy('id', 'desc')
                ->first();
        }

        // Get history for the active conversation (limit to last 20 initially)
        $messages = Message::withoutGlobalScope(\App\Modules\Core\Scopes\TenantScope::class)
            ->where('phone', $phone)
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $activeCustomer = Customer::where('phone', $phone)
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        // Check 24h window
        $canSend = $this->whatsapp->canSendTo($phone);

        return view('modules.whatsapp.show', compact('conversations', 'messages', 'activeCustomer', 'phone', 'canSend'));
    }

    /**
     * Fetch older messages for lazy loading.
     */
    public function fetchMessages($phone, Request $request)
    {
        $companyId = auth()->user()->company_id;
        $phone = $this->whatsapp->normalizePhone($phone);
        $beforeId = $request->query('before_id');

        $query = Message::withoutGlobalScope(\App\Modules\Core\Scopes\TenantScope::class)
            ->where('phone', $phone)
            ->where('company_id', $companyId);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $timezone = auth()->user()->company->timezone ?? config('app.timezone');

        return response()->json([
            'messages' => $messages->map(function ($msg) use ($timezone) {
                return [
                    'id' => $msg->id,
                    'message' => nl2br(e($msg->message)),
                    'direction' => $msg->direction,
                    'time' => $msg->created_at->timezone($timezone)->format('H:i')
                ];
            })
        ]);
    }

    /**
     * Send a message to a customer.
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        $companyId = auth()->user()->company_id;
        $success = $this->whatsapp->sendMessage($request->phone, $request->message, $companyId);

        if (!$success) {
            return back()->with('error', 'Could not send message. Check if 24h window is still open.');
        }

        return back()->with('success', 'Message sent.');
    }
}
