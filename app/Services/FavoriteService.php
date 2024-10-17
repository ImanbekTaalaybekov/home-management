<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FavoriteService
{
    public function addFavorite(Model $model, User $user): void
    {
        $favourite = new Favorite(['user_id' => $user->getKey()]);
        $model->favorites()->save($favourite);
    }

    public function hasExists(Model $model, User $user): bool
    {
        return $this->favoriteByUser($model, $user)->exists();
    }

    public function removeFavorite(Model $model, User $user): void
    {
        $this->favoriteByUser($model, $user)->delete();
    }

    protected function favoriteByUser(Model $model, User $user)
    {
        return $model->favorites()->whereHas('user', function ($query) use ($user) {
            return $query->whereUserId($user->getKey());
        });
    }
}
