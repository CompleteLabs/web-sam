<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::user()){
            return redirect('/masuk');
        }
        if(auth()->user()->role_id == 5 || auth()->user()->role_id == 4){
            return $next($request);
        }
        return redirect('/masuk');
    }
}

