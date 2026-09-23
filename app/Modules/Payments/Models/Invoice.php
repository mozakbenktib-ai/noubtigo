<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Companies\Models\Company;

class Invoice extends Model
{
    protected $fillable = [
        'company_id',
        'payment_id',
        'invoice_number',
        'subtotal',
        'discount',
        'coupon_id',
        'tax',
        'total',
        'currency',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
        'payment_id' => 'integer',
        'coupon_id' => 'integer',
        'discount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
