<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'is_global', 'description'];

    protected $casts = [
        'id' => 'integer',
    ];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Modules\Companies\Models\Company::class, 'role_company');
    }
}
