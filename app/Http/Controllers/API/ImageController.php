<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    // upload product images
    public function upload_product_images(Request $request, $id){}


    // delete product images
    public function get_product_images(Request $request, $id) {}


    // clear(delete) product images
    public function delete_product_images(Request $request, $id, $image_id = null) {}


    // upload business logo
    public function upload_business_logo(Request $request, $id, $image_id = null) {}


    // get business logo
    public function get_business_logo(Request $request, $id) {}
}
