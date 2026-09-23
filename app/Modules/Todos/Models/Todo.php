<?php

namespace App\Modules\Todos\Models;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'title',
        'notes',
        'priority',
        'visibility',
        'due_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
