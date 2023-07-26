<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Http\Requests\Api\UserRequest;
use App\Http\Requests\Api\UserRequest;
use App\Notifications\UserLogin;
use App\Notifications\APIPasswordResetNotification;
use App\Notifications\UserRegisterNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Auth;
use Twilio\Rest\Client;
use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\ReferalStats;
use App\Models\ApiPasswordResetToken;
// Include the other controller in this controller
use App\Http\Controllers\LoginSecurityController;
use Illuminate\Support\Facades\Validator;
use App\Models\History;
use App\Models\Deposits;
use DB;

class AuthController extends Controller
{
    /**
     * Log in
     */
    public function login(Request $request){

        //Validate request
        $request->validate([
            //'email' =>  'required|email|exists:users,email',
            'account' =>  'required',
            'password' =>   'required',
            //'g-recaptcha-response' => 'recaptcha',
            //'h-captcha-response' => 'required|HCaptcha'
        ]);
        
        //Validate by username or email to login
        $login_type = filter_var($request->account, FILTER_VALIDATE_EMAIL) 
        ? 'email' 
        : 'username';

        /* $request->merge([
            $login_type => $request->input('account')
        ]); */


        //$field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        /* if (Auth::attempt([$login_type => $request->account, 'password' => $request->password, 'activated' => 'false'])) {

            return response()->json([
                'code' => 12,
                'message' => 'Aun no has activado tu cuenta, por favor revisa tu correo y da click en el link de activación.',
                //'user' => $user
            ], 422);

        } */

        //Authenticate user request
        if(Auth::attempt([$login_type => strtolower($request->account), 'password' => $request->password])) {    

            //Validate if user is suspended
            //echo ($usuario=Auth::user());
            $user = Auth::user();

            $userLoginTime = User::updateOrCreate(
                ['id' => $user->id],
                ['last_access_time' => Carbon::now(),
                'last_access_ip' => $request->ip()]
            );
            //$user = User::where('email', $request->account)->orWhere('username', $request->account)->first();

            //Check if User has enabled Google 2fa

            if ($user->loginSecurity->google2fa_enable == 1) {
                $Google2fa = true;
                /* return response()->json([
                    '2fa' => $Google2fa
                    //'token' => $token->accessToken
                ],200); */

            } else {
                $Google2fa = false;
            }

            if ($user->status == 'suspended') {
      
                return response()->json([
                    'success' => false,
                    'errors' => $message = ['Su usuario se encuentra bloqueado. Comunícate con soporte.'],
                    //'user' => $user
                ], 406);
            }
            
            //Continue if validation was ok

            //$user = Auth::user();
            
            /* $revoke_id_token= \DB::table('oauth_access_tokens')->where('user_id', $user->id)->first();

            echo($revoke_id_token->user_id); */

            //Delete Token to end session

            $delete_token = \DB::table('oauth_access_tokens')->where('user_id', '=', $user->id)->delete();
            
            /* $revoke_tokens = \DB::table('oauth_access_tokens')->where('user_id', $user->id)->update(['revoked' => true]);

            echo($revoke_tokens); */

            //Update Login Attempts

            $user->login_attempts = 0;
            $user->save();

            //Add role as scope
            $userRole = $user->role()->first();

            $userIP= $request->getClientIp();

            //Token based on user role (scope)
            $token = $user->createToken($user->email . '_' . now(), [
                $userRole->role
            ]);

            $data = [
                'user' => $user->first_name." ".$user->last_name,
                'ip' => $userIP
            ];

            //Send email login notification only if role is different to.
            
            if ($userRole->role !== 'nodeserver') {
             $user->notify(new UserLogin($data));
            }
            

            //INICIO - ENVIO DE MENSAJES POR NEXMO - VONAGE

            /* try {
  
                $basic  = new \Nexmo\Client\Credentials\Basic(getenv("NEXMO_KEY"), getenv("NEXMO_SECRET"));
                $client = new \Nexmo\Client($basic);
      
                $receiverNumber = "593981073763";
                $message = "Hola Alex, Invierte en Wage Dollar";
      
                $message = $client->message()->send([
                    'to' => $receiverNumber,
                    'from' => 'Wage Dollar',
                    'text' => $message
                ]);
      
                //dd('SMS Sent Successfully.');
                  
            } catch (Exception $e) {
                //dd("Error: ". $e->getMessage());
            } */

            //FIN - ENVIO DE MENSAJES POR NEXMO - VONAGE

            //INICIO - ENVIO DE MENSAJES POR TWILIO

            /* $receiverNumber = "+593981073763";
            $message = "Invierte en Wage Dollar";
    
            try {
    
                $account_sid = getenv("TWILIO_SID");
                $auth_token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_number = getenv("TWILIO_NUMBER");
    
                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message]);
    
                dd('SMS Sent Successfully.');
    
            } catch (Exception $e) {
                dd("Error: ". $e->getMessage());
            } */

            //FIN - ENVIO DE MENSAJES POR TWILIO

            //ENVIO DE WHATASPP TWILIO 

            /* $receiverNumber = "whatsapp:+593981073763";
            $message = "Invierte en Wage Dollar";
    
            try {
    
                $account_sid = getenv("TWILIO_SID");
                $auth_token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_number = "whatsapp:+14155238886";
    
                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message]);
    
                //dd('SMS Sent Successfully.');
    
            } catch (Exception $e) {
                //dd("Error: ". $e->getMessage());
            } */

            //FIN ENVIO DE WHATSAPP TWILIO

            $active = Deposits::where('status', 'on')->where('user_id', $user->id)->exists();

            return response()->json([
                'token' => $token,
                'user' => $user,
                'IP' => $userIP,
                'twofa' => $Google2fa,
                'active' => $active
                //'token' => $token->accessToken
            ]);
            
        }else{

            //Validation for login attempts

            $user = User::where('email', $request->account)->orWhere('username', $request->account)->first();
            //echo $user;
            if ($user) {
                if ($user->login_attempts == 3) {

                    $user->status = 'suspended';
                    //echo($user->login_attempts);
                    $user->save();

                    return response()->json([
                        'success' => false,
                        'errors' => $message = ['Muchos intentos de inicio de sesión, su usuario se ha bloqueado. Comunícate con soporte.'],
                        //'user' => $user
                    ], 422);
                }
                $user->login_attempts = $user->login_attempts + 1;
                //echo($user->login_attempts);
                $user->save();
            }
            return response()->json([
                'success' => false,
                'errors' => $message = ['The provided credentials do not match our records.'],
                'data' => ''
            ],422);
            /* return response()->json([
                'message' => 'The provided credentials do not match our records.',
                //'user' => $user
            ], 422); */
            //return response()->json(['error' => 'Unauthenticated.'], 401);
        }

    }

    /**
     * Log Out
     */
    public function logout(Request $request)
    {
     /*  $user = Auth::user();
        
         $delete_token = \DB::table('oauth_access_tokens')->where('user_id', '=', $user->id)->delete();
        
        $btoken = $request->bearerToken(); */
        
        //Auth::logout();0
        //$userToken = $request->token;
        $token = $request->user()->token();
        //$userToken->revoke();
        $token->revoke();
        Session::flush();
        $response = [
            'message' => 'Ha cerrado sesión correctamente!',
            //'btoken' => $btoken,
            //'user' => $user
        ];
        return response($response, 200);

    }

    /**
     * Registration
     */
    //public function register(UserRequest $request)
    public function register(Request $request)
    {
        if ($request->referal) {
         

            $income = User::where('username', $request->referal)->whereNotNull('email_verified_at')->first();

            $resultValidate = $this->validateUser($request->referal);

            if ($resultValidate) {
                $rol = Role::where('user_id',  $income->id)->first();
                if($rol->role =='leader'){
      // return User::where('username', $request->referal)->whereNotNull('email_verified_at')->first();
        // Retrieve the validated input data...
        //$validated = $request->validated();

        /* $ids_transactions =  \DB::table('coinpayment_transactions')
        ->select('txn_id')
        ->where('status','0')
        ->get();
        foreach ($ids_transactions as $id_transaction) {
            \CoinPayment::getstatusbytxnid($id_transaction->txn_id);
        } */

        $request->validate([
            'username' => 'required|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'profession' => 'required',
            'type_invertion' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            /* 'cell_phone' =>  'required|regex:/(01)[0-9]{9}/|min:10', */
            'cell_phone' =>  'required',
            'country' => 'required',
            'state' => 'required'
        ]);
        $request->cell_phone;
        $activation_code = \Str::random(10);
        //$phone_activation_code = random_int(100000, 999999);

        $username = strtolower($request->username);
        $email = strtolower($request->email);
        $first_name = strtoupper($request->first_name);
        $last_name = strtoupper($request->last_name);
        $profession = strtolower($request->profession);
        $type_invertion = strtolower($request->type_invertion);
        $hashedUsername = \Hash::make($request->username, [
            'rounds' => 12,
        ]);

        $secretKey = \Crypt::encryptString($hashedUsername);
        
        $encryptSecretKey = \Hash::make($secretKey);

        $verifyUser = User::where('username', $username)->exists();
        $verifyEmail = User::where('email', $email)->exists();

        if ($verifyUser || $verifyEmail) {
           return response()->json([
                'success' => false,
                'errors' => $message = ['Usuario o correo ya existe, intenta con otro.']
            ], 422);
        }
        
        $user = User::create([
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'gender' => $request->gender,
            'email' => $email,
            'activation_code' => $activation_code,
            //'phone_activation_code' => $phone_activation_code,
            'password' => bcrypt($request->password),
            //'password' => $request->password,
            'profession' => $profession,
            'cell_phone' => $request->cell_phone,
            'ip_reg' => $request->ip(),
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'status' => 'on',
            'secret_key' => $encryptSecretKey,
            'cp_next_login' => false,
            'tour' => true
        ]);

       
       /*  $userRole = Role::create([
            'user_id' => $user->id,
            'role' => 'basic'
        ]); */
        if($type_invertion=='inversor'){
            $userRole = Role::create([
                'user_id' => $user->id,
                'role' => 'basic'
            ]);
        }else if($type_invertion=='red_inversor'){
            $userRole = Role::create([
                'user_id' => $user->id,
                'role' => 'leader'
            ]);
        }

        // Instantiate other controller class in this controller's method
        $create_2fa = new LoginSecurityController;
        // Use other controller's method in this controller's method
        $create_2fa->generate2faSecret($user);

        //check if the request has a Referal to save.
       
                $referal = ReferalStats::create([
                    'user_id' => $user->id,
                    'income' => $income->id,
                    'reg' => 0
                ]);

                $data = [
                    'user' => $user->first_name." ".$user->last_name,
                    'activation_code' => $activation_code
                ];
        
                //Send email notification with activation code
                $send_activation_code = $user->notify(new UserRegisterNotification($data));
               
               
                return response()->json  ([
                    'success' => true,
                    'message' => __('User register ok'),
                    'data' => ['secret_key' => $secretKey]
        
                ]);
                 }else {
                    return response()->json([
                        'success' => false,
                        'errors' => $message = ['El referido no cuenta con rol de líder.'],
                        //'user' => $user
                    ], 400);
                }
            }else {
                return response()->json([
                    'success' => false,
                    'errors' => $message = ['El referido no existe.'],
                    //'user' => $user
                ], 400);
            }
        }else {
            return response()->json([
                'success' => false,
                'errors' => $message = ['No cuenta con un referido.'],
                //'user' => $user
            ], 400);
        }

        // Data to send email notification
      


        /* return response()->json([
            'message' => __('User register ok'),
            'secret_key' => $secretKey
        ], 200); */
    }

    /**
     * Resend register email
     */
    public function resendRegister(Request $request){

        $request->validate([
            'account' =>  'required',
        ]);

        $user = User::where('email', $request->account)->orWhere('username', $request->account)->first();
        
        if ($user) {
            if ($user->activated) {
                return response()->json([
                    'message' => 'Usuario ya está activo',
                    //'user' => $user->email
                ], 500);
            }
            // Data to send email notification
            $data = [
                'user' => $user->first_name." ".$user->last_name,
                'activation_code' => $user->activation_code
            ];

            //Send email notification with activation code
            $send_activation_code = $user->notify(new UserRegisterNotification($data));
    
            return response()->json([
                'message' => 'Correo de activación de cuenta reenviado',
                'user' => $user->email
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Usuario NO encontrado',
        ], 422);
        

    }

    /**
     * Validate secret key to restore account
     */
    public function validateSecretKey(Request $request){

        $secretKey = $request->secret_key;

        //echo $secretKey;

        //$encryptSecretKey = \Hash::make($secretKey);

        //echo $secret_key;

        $hashed_secret_key= '$2y$10$u940yXTBcdJ9Y8Hpy6bmYuUjkEnPox7iYGMdCtwzLTF9sHcN7bHrO';

        if (\Hash::check($secretKey, $hashed_secret_key)) {

            return 1;

        }else {

            return 0;

        }
    }

    /**
     * Activate account
     */
    public function activateAccount(Request $request)
    {

        $request->validate([
            'activation_code' =>  'required',
        ]);

        $user = User::where('activation_code', $request['activation_code'])->first();

        //echo $user;

        if ($user) {

            $activateUser = User::updateOrCreate(
                ['id' => $user->id],
                ['email_verified_at' => Carbon::now(),
                'activated' => true]
            );
            //timer 
            sleep(4);

            return response()->json([
                'success' => true,
                'message' => 'Usuario activado correctamente.'
            ], 200);

        }else {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }

    public function activatePhone(Request $request)
    {

        $request->validate([
            'activation_code' =>  'required',
        ]);

        $user = User::where('activation_code', $request['activation_code'])->first();

        //echo $user;

        if ($user) {

            $activateUser = User::updateOrCreate(
                ['id' => $user->id],
                ['phone_verified_at' => Carbon::now(),
                'activated' => true]
            );
            //timer 
            sleep(4);

            return response()->json([
                'success' => true,
                'message' => 'Usuario activado correctamente.'
            ], 200);

        }else {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }
    /**
     * Send password reset token
     */
    public function sendPasswordResetToken(Request $request)
    {

        $request->validate([
            'account' =>  'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request['account'])->first();

        $secret_code = \Str::random(10);

        $encrypt_email = \Crypt::encryptString($user->email);

        $data = [
            'name' => $user->first_name." ".$user->last_name,
            'secret_code' => $secret_code,
            'email_token' => $encrypt_email
        ];

        //Send email notification with reset code
        $reset_link_sent = $user->notify(new APIPasswordResetNotification($data));

        if ($user) {

            $resetToken = ApiPasswordResetToken::updateOrCreate(
                ['user_id' => $user->id],
                ['secret_code' => \Crypt::encryptString($secret_code),
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'El token de reseteo de contraseña a sido enviado a tu email '.$user->email.', por favor ingrese la contraseña de reseteo en la página.'
            ], 200);

        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request){
        //Validate data from request
        $request->validate([
            'secret_code' =>  'required',
            'email_token' => 'required',
            'new_password' => 'required',
            'password_confirmation' => 'required|same:new_password',
        ]);
       
        //Decrypt the email passed from the request
        $decrypt_email = \Crypt::decryptString($request->email_token);

        $user = User::where('email', $decrypt_email)->first();

        if ($user) {

            $user_reset_token = ApiPasswordResetToken::where('user_id', '=', $user->id)->first();

            if ($user_reset_token) {
                
                $decrypt_secret_code = \Crypt::decryptString($user_reset_token->secret_code);

                if ($decrypt_secret_code == $request->secret_code) {

                    if (Carbon::now()->greaterThan($user_reset_token->expires_at)) {

                        return response()->json([
                            'success' => false,
                            'message' => 'El código ha expirado.'
                        ], 422);
                    }

                    //hacer update de password y enviar correo
            
                    $user->password = bcrypt($request->new_password);
                    $user->status = 'on';
                    $user->login_attempts = 0;
                    $updated = $user->save();
            
                    if ($updated)
                        return response()->json([
                            'success' => true,
                            'message' => 'Contraseña actualizada correctamente.',
                            'usuario' => $user
                        ]);
                    else
                        return response()->json([
                            'success' => false,
                            'message' => 'Password can not be updated'
                        ], 500);

                    /* return response()->json([
                        'success' => true,
                        'message' => 'Datos validados correctamente.'
                    ], 200); */

                }else {

                    return response()->json([
                        'success' => false,
                        'message' => 'El codigo NO es correcto.'
                    ], 422);

                }
            }else {

                return response()->json([
                    'success' => false,
                    'message' => 'No existe una solicitud de reestablecimiento para este usuario.'
                ], 422);
            }

        }

        return response()->json([
            'success' => false,
            'message' => 'Error.'
        ], 400);

    }

    /**
     * Validate username when creating a new user
     */
    public function validateUsername(Request $request){

        /* $validar = $request->validate([
            'username' => 'unique:users'
        ]); */

        $username = strtolower($request->username);

        $validar = User::where('username', $username)->first();

        //echo ($validar);
        if ($validar !== null) {
            return 1;
        }else {
            return 0;
        }
    }

    public function validateReferal(Request $request){

        /* $validar = $request->validate([
            'username' => 'unique:users'
        ]); */

        $username = strtolower($request->username);

        $validar = User::where('username', $username)->first();

        //echo ($validar);
        if ($validar !== null) {
            $role = Role::where('user_id', $validar->id)->first();
            if($role->role == 'leader'){
                return 1;
            }else{
                return 0;
            }
         
        }else {
            return 0;
        }
    }
    /**
     * Validate username when creating a new user
     */
    public function validateUser($ref){

        /* $validar = $request->validate([
            'username' => 'unique:users'
        ]); */

        $username = strtolower($ref);

        $validar = User::where('username', $username)->first();

        //echo ($validar);
        if ($validar !== null) {
            return 1;
        }else {
            return 0;
        }
    }

    /**
     * Validate email when creating a new user
     */
    public function validateEmail(Request $request){
        /* $request->validate([
            'email' => 'unique:users'
        ]); */

        $email = strtolower($request->email);

        $validar = User::where('email', $email)->first();

        //echo ($validar);
        if ($validar !== null) {
            return 1;
        }else {
            return 0;
        }
    }
    
    /**
     * Update language in the dashboard view
     */
    public function updateLanguage(Request $request){

        $request->validate([
            'lang' =>  'required',
        ]);

        $user = Auth::user();

        $updateLang = User::updateOrCreate(
            ['id' => $user->id],
            ['lang' => $request->lang]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Idioma actualizado correctamente'
        ], 200);
        
        return $user;
    }

    public function verifiedAuth(){
        return response()->json([
            'success' => false,
            'message' => ''
        ], 200);
    }

    

}
