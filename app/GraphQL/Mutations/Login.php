<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class Login
{
    /**
     * @param null $_
     * @param array<string, mixed> $args
     * @throws Error
     */
    public function __invoke($_, array $args): ?User
    {
        // Plain Laravel: Auth::guard()
        // Laravel Sanctum: Auth::guard(config('sanctum.guard', 'web'))
        $guard = Auth::guard(config('sanctum.guard', 'web'));
        if (! $guard->attempt($args, true)) {
            throw  new Error("Invalid credentials");
        }

        /**
         * Since we successfully logged in, this can no longer be `null`.
         *
         * @var User $user
         */
        $user = $guard->user();
        return $user->withToken($args["email"]);
    }
}
