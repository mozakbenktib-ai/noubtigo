<?php

namespace App\Modules\Payments\Providers;

use App\Modules\Payments\Contracts\PaymentProviderInterface;
use App\Modules\Payments\Models\Payment;

class ManualPaymentProvider implements PaymentProviderInterface
{
    public function getName(): string
    {
        return 'manual';
    }

    public function initializePayment(Payment $payment): array
    {
        // For manual payments, we just tell the user to transfer money.
        return [
            'status' => 'pending',
            'message' => 'Please transfer the amount to our bank account and contact support.',
            'reference' => 'manual_' . time(),
        ];
    }

    public function verifyPayment(Payment $payment, array $data): bool
    {
        // Manual payments are verified by admins manually.
        // This method might be called when an admin clicks "Approve" in the dashboard.
        return true;
    }

    public function refundPayment(Payment $payment): bool
    {
        // Manual refunds require manual bank transfers by the admin.
        return true;
    }
}
