<?php

namespace App\Modules\Payments\Managers;

use App\Modules\Payments\Contracts\PaymentProviderInterface;
use App\Modules\Payments\Providers\ManualPaymentProvider;
use Exception;

class PaymentManager
{
    /**
     * Resolve the payment provider instance based on string identifier.
     */
    public function resolve(string $provider): PaymentProviderInterface
    {
        switch (strtolower($provider)) {
            case 'manual':
            case 'virement':
            case 'chari_online':
                return new ManualPaymentProvider();
            
            // Future providers
            // case 'stripe':
            //     return new StripePaymentProvider();
            // case 'paypal':
            //     return new PaypalPaymentProvider();
            // case 'cmi':
            //     return new CmiPaymentProvider();
            
            default:
                throw new Exception("Payment provider [{$provider}] is not supported.");
        }
    }
}
