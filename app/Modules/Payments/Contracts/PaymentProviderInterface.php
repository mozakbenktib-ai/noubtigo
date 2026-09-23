<?php

namespace App\Modules\Payments\Contracts;

use App\Modules\Payments\Models\Payment;

interface PaymentProviderInterface
{
    /**
     * Get the name of the payment provider.
     */
    public function getName(): string;

    /**
     * Initialize a payment process.
     * Returns an array with necessary frontend/redirect data.
     */
    public function initializePayment(Payment $payment): array;

    /**
     * Verify if a payment was successful using callback/webhook data.
     */
    public function verifyPayment(Payment $payment, array $data): bool;

    /**
     * Refund a payment.
     */
    public function refundPayment(Payment $payment): bool;
}
