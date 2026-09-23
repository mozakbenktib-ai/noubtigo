<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'id' => 'integer',
        'invoice_id' => 'integer',
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
