<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
//use Auth;

class ValidatePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        //if they have a key...

        $loggedUser = auth('api')->user();

        $user = User::where('email', $loggedUser->email)->first();

        //return response()->json($user);

        $userPassword = $user->password;

        $password= $request->password;
       
        //return response()->json($password);
      

        if (\Hash::check($password, $userPassword)) {

            return $next($request);

        }else {

            return response()->json([
                'status' => false,
                'errors' => $message = ['Contraseña incorrecta.']
            ],422);

        }

        //return $next($request);
    }
}
