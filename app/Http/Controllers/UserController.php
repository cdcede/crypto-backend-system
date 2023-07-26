<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Deposits;
use App\Models\History;
use App\Models\UserBalances;
use App\Models\ReferalStats;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Http;
use Hexters\CoinPayment\CoinPayment;
use App\Notifications\UserRegisterNotification;
use Carbon\Carbon;
use Twilio\Rest\Client;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = User::orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function usersTableFilter(Request $request)
    {
       
        $order = $request->order??'desc';
        $page_numbers = $request->page_numbers;
        $word_filter = $request->word_filter;
        $verified = $request->verified;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $myObj= new \stdClass();
        $myObj2= new \stdClass();
            switch ($request->column_name) {
            case 'id_document':
                $column_name =  'id';
            break;
            case 'name':
                $column_name =  'last_name';
            break;
            case 'actions':
                $column_name =  'id';
            break;
            case 'status':
                $column_name =  'id';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

                $users = User::with('role')
                ->select('users.*','roles.role as rol')
                ->join('roles','users.id','roles.user_id')
                ->where('verified','like','%'.$verified.'%')
                ->wordFilter($word_filter,['username','first_name','last_name'],'updated_at',$from_date,$to_date)
                ->orderBy($column_name, $order)
                ->paginate($page_numbers);
                foreach ($users as $user) {
                    $myObj->username= $user->username;
                    $myObj->first_name= $user->first_name; 
                    $myObj->last_name= $user->last_name; 
                    $myObj->user_id= $user->id; 
                    $user->id_document = json_decode(json_encode((array)$myObj),true);
                    $user->name = $user->last_name.' '.$user->first_name; 
                    $user->actions = json_decode(json_encode($user),true);
                }

        if ($users) {

            return response()->json([
                'success' => true,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $users
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No data'
        ],500);
    }

    /**
     * Show the form for creating a new resource. 
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|unique:users',
            'first_name' => 'required',
            'profession' => 'required',
            'type_invertion' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users',
            'cell_phone' => 'required',
            'password' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
        ]);
 
        $user = new User();
        $user->username = strtolower($request->username);
        $user->first_name = strtoupper($request->first_name);
        $user->profession = strtoupper($request->profession);
        $user->type_invertion = $request->type_invertion;
        $user->last_name = strtoupper($request->last_name);
        $user->email = strtolower($request->email);
        $user->cell_phone = $request->cell_phone;
        $user->password = $request->password;
        $user->country = $request->country;
        $user->state = $request->state;
        $user->city = $request->city;
        $user->ip_reg = '0.0.0.0';
        $user->status = 'on';
        $user->cp_next_login = false;

        //$user = User::create($request->all());
 
        if ($user->save())
            return response()->json([
                'success' => true,
                'data' => $user->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'User not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
 
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $user->toArray()
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        /* $user = Plan::find($id);
 
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 400);
        }

 
        $updated = $plan->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'User can not be updated'
            ], 500); */

        try {
            $user = User::findOrFail($id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 403);
        }

        //$user->update($request->all());

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->save();
        
        return response()->json(['message' => 'Usuario actualizado con exito.']);
    }

    public function userUpdate(Request $request){

        try {
            $user = User::findOrFail($request->id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 403);
        }

        //$user->update($request->all());

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->save();

        return response()->json(['message' => 'Usuario actualizado con exito.']);
    }
    
    public function userUpdateByAdmin(Request $request){
        
        try {
            $user = User::findOrFail($request->id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 403);
        }

        $hashedUsername = \Hash::make($request->username, [
            'rounds' => 12,
        ]);

        $secretKey = \Crypt::encryptString($hashedUsername);
        
        $user->username = ($request->username!=null)?strtolower($request->username):$user->username;
        $user->password = ($request->password!=null)?bcrypt($request->password):$user->password;
        $user->first_name = ($request->first_name!=null)?strtoupper($request->first_name):$user->first_name;
        $user->last_name = ($request->last_name!=null)?strtoupper($request->last_name):$user->last_name;
        $user->email = ($request->email!=null)?strtolower($request->email):$user->email;
        $user->status = ($request->status!=null)?$request->status:$user->status;
        $user->profession = ($request->profession!=null)?$request->profession:$user->profession;
        $user->type_invertion = ($request->type_invertion!=null)?$request->type_invertion:$user->type_invertion;
        $user->city = ($request->city!=null)?$request->city:$user->city;
        if($request->password!=null){
            $user->password =  bcrypt($request->password);
        }
        $user->state = ($request->state!=null)?$request->state:$user->state;
        $user->country = ($request->country!=null)?$request->country:$user->country;
        $user->identification_card = ($request->identification_card!=null)?$request->identification_card:$user->identification_card;
        $user->cp_next_login = ($request->cp_next_login!=null)?$request->cp_next_login:$user->cp_next_login;
        $user->lang = ($request->lang!=null)?$request->lang:$user->lang;
        $user->save();

        return response()->json(['message' => 'Usuario actualizado con exito.']);
    
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $user = User::find($id);
 
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 400);
        }
 
        if ($user->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User can not be deleted'
            ], 500);
        }
    }

    public function changePassword(Request $request){

        $this->validate($request, [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_new_password' => 'required|same:new_password',
        ]);

        $user = Auth::user();

        if (\Hash::check($request->old_password, $user->password)) {

            $updateUserPassword = User::find($user->id);

            $updateUserPassword->password = bcrypt($request->new_password);

            $updateUserPassword->cp_next_login = false;

            $updateUserPassword->save();

            return response()->json([
                'status' => true,
                'message' => 'Contraseña actualizada correctamente'
            ], 200);

        }else {

            return response()->json([
                'status' => false,
                'message' => 'Contraseña anterior es incorrecta'
            ], 400);

        }
    }

    public function uploadKYC(Request $request){
     
        $this->validate($request, [
            'superior' => 'required|mimes:jpg,jpeg,png',
            'posterior' => 'required|mimes:jpg,jpeg,png',
            'identification_card' => 'required|nullable|min:10',
        ]);

        $user = Auth::user();

        if ($request->hasFile('superior') && $request->hasFile('posterior')) {
            if ( $user->verified != 3) {
                $superior = $request->file('superior')->storeAs(
                    $user->username, 'superior'
                );
                $posterior = $request->file('posterior')->storeAs(
                    $user->username, 'posterior'
                );

                $user->verified = 1;
                $user->identification_card = $request->identification_card;
                $user->save();
        
                return response()->json([
                    'status' => true,
                    'message' => 'Imagenes subidas correctamente.'
                ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Usted ya ha sido verificado'
                ], 422);
            }
        }else {
            return response()->json([
                'status' => false,
                'message' => 'No has subido las 2 imagenes'
            ], 422);
        }

        
    }

    public function getKYC(Request $request){

        if (Storage::disk('local')->exists($request->username.'/superior') && Storage::disk('local')->exists($request->username.'/posterior')) {
            
            $imageFront = $request->username.'/superior';
            $imageBack = $request->username.'/posterior';

            $full_path_front = Storage::path($imageFront);
            $full_path_back = Storage::path($imageBack);

            $base64Front = base64_encode(Storage::get($imageFront));
            $base64Back = base64_encode(Storage::get($imageBack));
            
            $image_data_front = 'data:'.mime_content_type($full_path_front) . ';base64,' . $base64Front;
            $image_data_back = 'data:'.mime_content_type($full_path_back) . ';base64,' . $base64Back;

            return response()->json([
                'superior' => $image_data_front,
                'posterior' => $image_data_back
            ], 200);

        }else {
            return response()->json([
                'error' => 'No existen los archivos en el servidor.'
            ], 422);
        }
    }

    public function uploadAvatar(Request $request){

        $this->validate($request, [
            'avatar' => 'required',
        ]);
        
        $user = Auth::user();

        if ($request->hasFile('avatar')) {

            $avatar = $request->file('avatar')->storeAs(
                $user->username, 'avatar'
            );
    
            return response()->json([
                'status' => true,
                'message' => 'Imagenes subidas correctamente.'
            ], 200);

        }else {
            return response()->json([
                'status' => false,
                'message' => 'Error'
            ], 422);
        }
    }

    public function getAvatar(Request $request){
        
        if (Storage::disk('local')->exists($request->username.'/avatar')) {
            
            $imageFront = $request->username.'/avatar';

            $full_path_front = Storage::path($imageFront);

            $base64Front = base64_encode(Storage::get($imageFront));
            
            $image_data_front = 'data:'.mime_content_type($full_path_front) . ';base64,' . $base64Front;

            return response()->json([
                'avatar' => $image_data_front,
            ], 200);

        }else {
            return response()->json([
                'error' => 'No existen los archivos en el servidor.'
            ], 422);
        }
    }

    public function validToken(){

        $respuesta = 'Token is valid';
        
        return response()->json([
            'message' => $respuesta]
        );

    }

    public function verifyKYC(Request $request){

        try {
            $user = User::findOrFail($request->user_id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 403);
        }

   /*      $this->validate($request, [
            'identification_card' => 'required|unique:users',
        ]);
 */
        //$user->update($request->all());
        if ($request->identification_card) {
            $user->identification_card = $request->identification_card;
            $user->verified = 3;
        }else {
            $user->verified = 2;
        }      
    
        $user->save();

        return response()->json(['message' => 'Usuario actualizado con exito.']);

        

    }

    public function addReferrals(Request $request){

        $authUser = Auth::user();

        // Retrieve the validated input data...

        $request->validate([
            'username' => 'required|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'profession' => 'required',
            'type_invertion' => 'required',
            'email' => 'required|email|unique:users',
            'cell_phone' => 'required',
            'country' => 'required',
            'state' => 'required'
        ]);
       
        $activation_code = \Str::random(10);

        $temporal_password = \Str::random(12);
        $temporal_password = $temporal_password.'1@aC';

        $username = strtolower($request->username);
        $profession = strtolower($request->profession);
        $type_invertion = strtolower($request->type_invertion);
        $email = strtolower($request->email);
        //$gender = $request->gender;
        $first_name = strtoupper($request->first_name);
        $last_name = strtoupper($request->last_name);
        
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
            'came_from' => $authUser->id,
            'activation_code' => $activation_code,
            'profession' => $profession,
            'password' => bcrypt($temporal_password),
            //'password' => $request->password,
            'cell_phone' => $request->cell_phone,
            'ip_reg' => $request->ip(),
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'status' => 'on',
            'secret_key' => $encryptSecretKey,
            'cp_next_login' => true,
            'tour' => true
        ]);

        
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
        /*         if ($request->referal) {

                    $income = User::where('username', $request->referal)->first();

                    $resultValidate = $this->validateUser($request->referal);

                    if ($resultValidate) {

                        $referal = ReferalStats::create([
                            'user_id' => $user->id,
                            'income' => $income->id,
                            'reg' => 0
                        ]);
                        
                    }

                }
        */
        //$income = User::where('username', $request->referal)->first();

        $referal = ReferalStats::create([
            'user_id' => $user->id,
            'income' => $authUser->id,
            'reg' => 0
        ]);
                // Data to send email notification
        $data = [
            'user' => $user->first_name." ".$user->last_name,
            'activation_code' => $activation_code,
            'cp_next_login' => $user->cp_next_login,
            'temporal_password' => $temporal_password
        ];

        //Send email notification with activation code
        $send_activation_code = $user->notify(new UserRegisterNotification($data));
 
        return response()->json([
            'message' => 'Usuario creado correctamente',
            'secret_key' => $secretKey
        ], 200);

    }

    public function sendSmsActivationCode(Request $request){
        
        $user = Auth::user();
        $phone_activation_code = random_int(100000, 999999);
        $user->phone_activation_code = $phone_activation_code;
        $user->save();
        //$receiverNumber = $user->cell_phone;
        $receiverNumber = $request->cell_phone;
        $message = "[Wage Dollar International] Hola $user->first_name $user->last_name. Para validar su numero de telefono ingrese el siguiente codigo: $user->phone_activation_code.";

        try {

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");

            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => $message]);

            //dd('SMS Sent Successfully.');

        } catch (Exception $e) {
            //dd("Error: ". $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado un SMS con el codigo de verificacion a tu numero de celular registrado.'
        ]);
       
    }

    public function validatePhoneNumber(Request $request){

        $request->validate([
            'phone_activation_code' => 'required',
        ]);

        $user = Auth::user();

        $new_phone_activation_code = random_int(100000, 999999);

        if ($request->phone_activation_code == $user->phone_activation_code) {

            $user->phone_activation_code = $new_phone_activation_code;
            $user->phone_verified_at = Carbon::now();
            if($user->save()){
                return response()->json([
                    'success' => true,
                    'message' => 'Celular validado correctamente'
                ]);
            }

        }else{
            return response()->json([
                'success' => false,
                'errors' => $message = ['Codigo de validacion incorrecto']
            ],422);
        }

    }
}
