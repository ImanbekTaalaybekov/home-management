<?php

namespace App\Policies;

use App\Enums\OrchidPermissionEnum;
use App\Models\BuildingCategory;
use App\Models\User;
use App\Orchid\PlatformProvider;

class BuildingCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BuildingCategory $buildingCategory): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BuildingCategory $buildingCategory): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BuildingCategory $buildingCategory): bool
    {
        return $user->hasAccess(OrchidPermissionEnum::BUILDING_CATEGORY_PERMISSION->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BuildingCategory $buildingCategory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BuildingCategory $buildingCategory): bool
    {
        return false;
    }
}
