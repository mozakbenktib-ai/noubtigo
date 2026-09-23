<?php

namespace App\Modules\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Companies\Models\Company;
use App\Modules\Core\Traits\BelongsToTenant;

class Message extends Model
{
    use BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'message',
        'direction',
        'company_id',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the company that the message belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
