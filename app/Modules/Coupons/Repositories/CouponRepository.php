<?php

namespace App\Modules\Coupons\Repositories;

use App\Modules\Coupons\Models\Coupon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CouponRepository implements CouponRepositoryInterface
{
    /**
     * Paginate coupons with filter conditions.
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Coupon::query();

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['category'])) {
            $query->where('coupon_category', $filters['category']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Retrieve all coupons.
     */
    public function all(): Collection
    {
        return Coupon::all();
    }

    /**
     * Find a coupon by UUID.
     */
    public function findByUuid(string $uuid): ?Coupon
    {
        return Coupon::where('uuid', $uuid)->first();
    }

    /**
     * Find a coupon by Code.
     */
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', $code)->first();
    }

    /**
     * Create a new coupon in database.
     */
    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    /**
     * Update an existing coupon.
     */
    public function update(Coupon $coupon, array $data): bool
    {
        return $coupon->update($data);
    }

    /**
     * Soft delete a coupon.
     */
    public function delete(Coupon $coupon): bool
    {
        return $coupon->delete();
    }
}
