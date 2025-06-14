<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // get customers
    public function customers(Request $request){}


    // get vendors
    public function vendors(Request $request){}


    // user statistics
    public function admins(Request $request){}


    // user statistics
    public function statistics(Request $request){}


    // udpate user profile
    public function update($id, Request $request) {}


    // get user profile
    public function show($id, Request $request){}


    // get user profile
    public function search(Request $request){}


    // change user password
    public function change_password(Request $request) {}
    
}
