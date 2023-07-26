<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Auth;
use App\Models\User;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //if they have a key...
        $account = strtolower($request->account);

        if ($request->email_token) {
            $decryptEmailToken = \Crypt::decryptString($request->email_token);
            //echo ($decryptEmailToken);
            $account = $decryptEmailToken;
        }

        $user = User::where('email', $account)->orWhere('username', $account)->first();

        if (!$user) {
            $response = [
                'status' => false,
                'message' => 'Cuenta NO encontrada.',
            ];
    
            return response()->json($response, 422);
        }

        if ($user->isNotActivated()) {
            
            $response = [
                'status' => false,
                'message' => 'Aún NO has activado tu cuenta, por favor revisa tu correo electrónico.',
            ];
    
            return response()->json($response, 403);
        }

        return $next($request);

    }
}
