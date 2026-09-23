<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'is_global', 'module', 'description', 'company_id'];

    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
    ];


    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Modules\Companies\Models\Company::class, 'permission_company');
    }
}
