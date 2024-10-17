<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use \Illuminate\Support\Str;

class CustomDatabaseTokenRepository extends DatabaseTokenRepository
{
    public function createNewToken()
    {
        return sprintf('%s-%s', strtoupper(Str::random(4)), strtoupper(Str::random(4)));
    }
}
