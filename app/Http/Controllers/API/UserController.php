<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // get customers
    public function customers(Request $request){
        $users = User::whereNotIn('type', ['admin', 'agent'])->get();
        return response()->json(['data'=>UserResource::collection($users), 'message'=>'success'], 200);
    }


    // get vendors
    public function vendors(Request $request){
        $users = User::join('businesses', ['businesses.user_id'=>'users.id'])->whereNotIn('type', ['admin', 'agent'])->get();
        return response()->json(['data'=>UserResource::collection($users), 'message'=>'success'], 200);
    }


    // user statistics
    public function admins(Request $request){
        $users = User::where('type', 'admin')->get();
        return response()->json(['data'=>UserResource::collection($users), 'message'=>'success'], 200);
    }


    // user statistics
    public function statistics(Request $request){}


    // udpate user profile
    public function update($id, Request $request) {
        $user = User::find($id);
        if($user != null){
            $update = [];
            if($request->name != null) $update['name'] = $request->name;
            if($request->tel != null) $update['tel'] = $request->tel;
            if($request->email != null) $update['email'] = $request->email;
            if($request->type != null) $update['type'] = $request->type;
        }
        if(count($update) > 0){
            $user->update($update);
        }
        if($user != null)
            return response()->json(['data'=>$user, 'message'=>'success'], 200);
        else
            return response()->json(['data'=>[], 'message'=>"No user item found with given ID"], 400);

    }


    // get user profile
    public function show($id, Request $request){
        $user = User::find($id);
        return response()->json(['data'=>$user, 'message'=>'success'], 200);
    }


    // get user profile
    public function search(Request $request){
        $key = $request->search_key;
        if(!empty($key)){
            $users = User::where('name', 'like', '%'.$key.'%')->orWhere('tel', 'like', '%'.$key.'%')->orWhere('email', 'like', '%'.$key.'%')->orderBy('id', 'desc')->get();
            return response()->json(['data'=>UserResource::collection($users), 'message'=>'success'], 200);
        }
    }


    // change user password
    public function change_password(Request $request) {}
    
}
