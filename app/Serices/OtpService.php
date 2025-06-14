<?php

namespace App\Serices;

use App\Models\OtpTrack;

class OtpService{


    // constructor
    public function __construct() {}


    // genetate otp token
    public function generate(int $user_id, String $type, int $length) {
        $otp = '';
        if($length == 0){$length == 6;}
        if(!in_array($type, ['numeric', 'alphanumerix'])){
            $type = 'alphanumeric';
        }
        if($type == 'numeric'){
            for ($i=1; $i <= $length ; $i+1) { 
                # code...
                $otp .= rand(0, 9);
            }
        }elseif($type == 'alphanumeric'){
            for ($i = 1; ($i <= $length/2 || strlen($otp) < $length) ; $i++) { 
                # code...
                
                $otp .= range('A', 'Z')[rand(0, 25)];
                if(strlen($otp) < $length){
                    $otp .= rand(0, 9);
                }
            }
        }
        if(strlen($otp) > 0){
            $otp_track = ['user_id' => $user_id, 'otp' => $otp, 'created_at' => now(), 'expires_at' => now()->addMinutes(30), 'used' => 0];
            $otp_instance = OtpTrack::create($otp_track);
            return $otp_instance;
        }
        return null;
    }

    // validate otp token
    public function validate($user_id, String $otp) {
        $otp_track = OtpTrack::where(['user_id' => $user_id, 'otp' => $otp])->first();
        if(empty($otp_track)){
            throw new \Exception("Invalid OTP");
        }
        if(now()->isAfter($otp_track->expires_at)){
            throw new \Exception("OTP has expired.");
        }
        if($otp_track->used == 1){
            throw new \Exception("OTP already used");
        }

        return $otp_track;
    }
}