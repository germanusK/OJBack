<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Agent;
use App\Models\OtpTrack;
use App\Models\User;
use App\Serices\NotificationService;
use App\Serices\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public $otpService;
    public $notificationService;

    public function __construct(OtpService $otpService, NotificationService $notificationService){
        $this->otpService = $otpService;
        $this->notificationService = $notificationService;
    }


    // user creation action
    public function register(Request $request){
        $request->validate(['name' => 'required', 'tel' => 'required|string|min:9|unique:users,tel', 'password'=>'required|min:6', 'confirm_password'=>'required|same:password', 'email'=>'nullable|email|unique:users,email', 'type'=>'nullable']);

        $data = [
            'name'=>$request->name, 'email' => $request->email, 'type' => $request->type, 'password' => Hash::make($request->password), 'code' => $request->code
        ];
        $data['tel'] = str_replace(['+', ' '], '', $request->get('tel'));
        
        if(User::where(['tel'=>$data['tel']])->count() > 0){
            return response()->json(['message'=>'User with the same phone number already exist'], 400);
        }
        if(User::where(['email'=>$data['email']])->count() > 0){
            return response()->json(['message'=>'User with the same email already exist'], 400);
        }
        $user  = User::create($data);
        $token = $user->createToken('authToken')->accessToken;
        $data = [
            'user' => new UserResource($user),
            'token' => $token,
            'message' => "User account was successfully created"
        ];
        return response()->json($data, 200);        
    }


    // user login
    public function login(Request $request){
        $request->validate(['tel'=>'required', 'password'=>'required|min:6']);

        $user = User::where('tel', $request->tel)->first();
        if(isset($user) and Hash::check($request->password, $user->password)){
            $token = $user->createToken('authToken')->accessToken;
            $data = [
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'success'
            ];
            return response()->json($data, 200);
        }
        if(isset($user)){
            return response()->json(['message' => 'Could not login user. Wrong username or password'], 400);
        }else{
            return response()->json(['message' => 'Could not login user. No account exists with given username or password'], 400);
        }
    }


    /**
     * PASSWORD RESET PROCESS
     * -recieve account phone number (for sms) or email address to send a reset OTP
     * -validate creds, send OTP and respond with a reset_id (the user account id to which the supplied email/phone is associated)
     * -recieve reset_id together with the sent OTP
     * -validate OTP and reset_id and respond with reset_id and tracker_id (OTP instance db id) if OTP valid
     * -recieve the reset_id, the tracker_id and the new password and its confirmation, set the password and logs in the user if all is OK
     */

    // forgot password
    public function forgot_password(Request $request){
        $request->validate(['name'=>'required', 'email' => 'email|nullable', 'tel' => 'nullable']);
        if(empty($request->email) and empty($request->tel)){
            $data = ['message' => 'No channel to send password reset instructions. You must specify either phone number or email address.'];
            return response()->json($data, 200);
        }
        if($request->tel != null){$notification = ['channel' => 'SMS', 'address' => $request->tel];}
        elseif ($request->email != null) {$notification = ['channel' => 'EMAIL', 'address' => $request->email];}

        $check = ['name' => $request->name];
        if($request->email != null){$check['email'] = $request->email;}
        if($request->tel != null){$check['tel'] = $request->tel;}

        $user = User::where($check)->first();
        if(!empty($user)){
            $token = $this->otpService->generate($user->id, 'alphanumeric', 6);
            $notification['otp'] = $token->otp;
            $notification['type'] = "PASSWORD_RESET";

            $this->notificationService->sendNotification($notification);
            return response()->json(['reset_id' => $user->id, 'message' => 'OTP sent'], 200);
        }
        return response()->json(['message' => 'Operation failed. No user matches provided information'], 400);
    }


    // validate otp
    public function validate_otp(Request $request){
        $request->validate(['otp' => 'required', 'reset_at' => 'required']);

        try {
            $otp_track = $this->otpService->validate($request->reset_id, $request->otp);
            $data = ['reset_id' => $request->reset_id, 'tracker_id' => $otp_track->id];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            //throw $th;
            $data = ['message' => "Oeration failed. ".$th->getMessage()];
            return response()->json($data, 400);
        }
    }


    // Change password
    public function reset_password(Request $request){

        $request->validate(['reset_id' => 'required', 'tracker_id' => 'tracker_id', 'new_password' => 'required', 'password_confirmation' => 'required|same:new_password']);

        $otp_track = OtpTrack::find($request->tracker_id);
        if(!empty($otp_track)){
            $otp_track->update(['used' => 1]);
        }

        $user = User::find($request->reset_id);
        if(!empty($user)){
            $user->update(['password' => Hash::make($request->password)]);
            $token = $user->createToken('authToken')->accessToken;
            return response()->jspon([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'You have successfully reset your account password'
            ], 200);
        }

        return response()->json(['message' => 'Operation failed. Error occured']);
    }
}
