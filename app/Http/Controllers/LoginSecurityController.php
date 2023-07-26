<?php

namespace App\Http\Controllers;

use App\Models\LoginSecurity;
use Auth;
use Hash;
use Crypt;
use DB;
use Illuminate\Http\Request;

class LoginSecurityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show 2FA Setting form
     */

     public function showHistory(Request $request){
        $user = Auth::user();
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $histories = DB::table('oauth_access_tokens')
        ->where('revoked',false)
        ->paginate($page_numbers);

        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente.',
            'data' => $histories
        ]);
     }
     
    public function show2faForm(Request $request){
        $user = Auth::user();
        $google2fa_url = "";
        $secret_key = "";

        //echo($user->loginSecurity);   
        //$google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
            
        /* return $google2fa->generateSecretKey();

        $user->google2fa_secret = $google2fa->generateSecretKey(); */

        if($user->loginSecurity()->exists()){
            $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
            $google2fa_url = $google2fa->getQRCodeInline(
                'Wage Dollar',
                $user->email,
                $user->loginSecurity->google2fa_secret
                //Crypt::decrypt($user->loginSecurity->google2fa_secret)
            );
            $secret_key = $user->loginSecurity->google2fa_secret;
            //$secret_key = Crypt::decrypt($user->loginSecurity->google2fa_secret);
        }

        $data = array(
            'user' => $user,
            'secret' => $secret_key,
            'google2fa_url' => $google2fa_url
        );

        //echo('aaaaa');
        //return view('auth.2fa_settings')->with('data', $data);

        return response()->json(['data' => $data], 200);
    }

    /**
     * Generate 2FA secret key
     */
    public function generate2faSecret($data){
        $user = $data;
        //echo($user);  
        // Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
        
        // Add the secret key to the registration data
        $login_security = LoginSecurity::firstOrNew(array('user_id' => $user->id));
        $login_security->user_id = $user->id;
        $login_security->google2fa_enable = 0;
        $login_security->google2fa_secret = $google2fa->generateSecretKey();
        //$login_security->google2fa_secret = Crypt::encrypt($google2fa->generateSecretKey());
        $login_security->save();

        return response()->json(['msg' => 'Secret key is generated.'], 200);
        //return redirect('/2fa')->with('success',"Secret key is generated.");
    }

    /* public function generate2faSecret(Request $request){
        $user = Auth::user();
        //echo($user);  
        // Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
        
        // Add the secret key to the registration data
        $login_security = LoginSecurity::firstOrNew(array('user_id' => $user->id));
        $login_security->user_id = $user->id;
        $login_security->google2fa_enable = 0;
        $login_security->google2fa_secret = $google2fa->generateSecretKey();
        //$login_security->google2fa_secret = Crypt::encrypt($google2fa->generateSecretKey());
        $login_security->save();

        return response()->json(['msg' => 'Secret key is generated.'], 200);
        //return redirect('/2fa')->with('success',"Secret key is generated.");
    } */


    /**
     * Enable 2FA
     */
    public function enable2fa(Request $request){
        $user = Auth::user();
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $secret = $request->input('secret');
        $valid = $google2fa->verifyKey($user->loginSecurity->google2fa_secret, $secret);
        //$valid = $google2fa->verifyKey(Crypt::decrypt($user->loginSecurity->google2fa_secret), $secret);

        if($valid){
            $user->loginSecurity->google2fa_enable = 1;
            $user->loginSecurity->save();

            return response()->json(['msg' => '2FA is enabled successfully.'], 200);

            //return redirect('2fa')->with('success',"2FA is enabled successfully.");

        }else{

            return response()->json(['error' => 'Invalid verification Code, Please try again.'], 422);
            //return redirect('2fa')->with('error',"Invalid verification Code, Please try again.");
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2fa(Request $request){
        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            // The passwords matches

            return response()->json(['error' => 'Your password does not matches with your account password. Please try again.'], 422);
            //return redirect()->back()->with("error","Your password does not matches with your account password. Please try again.");
        }

        $validatedData = $request->validate([
            'current_password' => 'required',
        ]);
        $user = Auth::user();
        $user->loginSecurity->google2fa_enable = 0;
        $user->loginSecurity->save();

        return response()->json(['msg' => '2FA is now disabled.'], 200);
        //return redirect('/2fa')->with('success',"2FA is now disabled.");

    }
}
