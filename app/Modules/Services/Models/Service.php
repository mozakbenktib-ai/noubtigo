<?php

namespace App\Modules\Services\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Support\Str;
use App\Modules\Core\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, BelongsToTenant, HasTranslations;

    protected $fillable = [
        'company_id',
        'name',
        'prefix',
        'slug',
        'description',
        'price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'id' => 'integer',
        'company_id' => 'integer',
    ];

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($service) {
            if (empty($service->uuid)) {
                $service->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Use UUID for route model binding generation.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Resolve the route binding to accept either ID or UUID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (\Illuminate\Support\Str::isUuid($value)) {
            return $this->where('uuid', $value)->firstOrFail();
        }
        return $this->where($field ?? 'id', $value)->firstOrFail();
    }
}
