<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'payment_id',
        'transaction_reference',
        'gateway_response',
        'status',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'id' => 'integer',
        'payment_id' => 'integer',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
