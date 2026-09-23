<?php

namespace App\Modules\Coupons\Repositories;

use App\Modules\Coupons\Models\Coupon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CouponRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function all(): Collection;
    public function findByUuid(string $uuid): ?Coupon;
    public function findByCode(string $code): ?Coupon;
    public function create(array $data): Coupon;
    public function update(Coupon $coupon, array $data): bool;
    public function delete(Coupon $coupon): bool;
}
