<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventPlayerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userType = UserType::from($user->type);

            // If user is a player, deny access to admin routes
            if ($userType === UserType::Player) {
                Auth::logout();
                
                return redirect()->route('login')
                    ->with('error', 'Players are not allowed to access the admin panel. Please use the player application.');
            }
        }

        return $next($request);
    }
}

