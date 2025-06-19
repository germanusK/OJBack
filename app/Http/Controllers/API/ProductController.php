<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    protected $user;
    public function __construct()
    {
        $this->middleware('auth_api');
        $this->user = request()->user('auth_api');
    }

    //get list of business
    public function index(Request $request) {
        $products = \App\Models\Product::whereNotNull('business_id');
        if(!empty($request->business_id)){
            $products = $products->where('business_id', $request->business_id);
        }
        if(!empty($request->category_id)){
            $products = $products->where('category_id', $request->category_id);
        }
        if(!empty($request->type)){
            $products = $products->where('type', $request->type);
        }

        $page_size = request('page_size', 50);
        $products = $products->paginate($page_size);

        return response()->json(['data' => $products->items(), 'previousPageUrl' => $products->previousPageUrl(), 'nextPageUrl' => $products->nextPageUrl(), 'currentPage' => $products->currentPage() , 'pages' => $products->total()/$page_size, 'message' => "success"], 200);
    }


    // get product/service item
    public function show(Request $request, $id) {
        $product  = \App\Models\Product::find($id);

        if(!empty($product)){
            return response()->json(['data' => $product->toArray(), 'message' => "success"], 200);
        }
        return response()->json(['data' => null, 'message' => "No product found with given id"], 400);
    }


    // create product
    public function create(Request $request) {
        
        $request->validate(['name' => 'required', 'business_id' => 'required', 'category_id' => 'required', 'type' => 'required']);

        $data = [
            'name' => $request->name,
            'type' => $request->type,
            'business_id' => $request->business_id,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price
        ];

        if(\App\Models\Product::where(['name' => $data['name'], 'business_id' => $request->business_id, 'type' => $request->type])->count() > 0){
            return response()->json(['data' => null, 'message' => "An item of type ".$data['type']." with name ".$data['name']." already exist for the given business"], 400);
        }

        $item = \App\Models\Product::create($data);
        return response()->json(['data' => $item->toArray(), 'message' => "success"], 200);
    }


    // update product item
    public function update(Request $request, $id) {

        $product = \App\Models\Product::find($id);

        if($this->user->id != $product->business->user_id and $this->user->type != 'admin'){
            return response()->json(['data'=>null, 'message'=>"Permission denied. You are not allowed to make changes to this business"], 400);
        }

        $data = [
            'name' => request('name', $product->name),
            'type' => request('type', $product->type),
            'description' => request('description', $product->description),
            'category_id' => request('category_id', $product->category_id),
            'business_id' => request('business_id', $product->business_id),
            'quantity' => request('quantity', $product->quantity),
            'price' => request('price', $request->price),
        ];

        if(\App\Models\Product::whereNot('id', $id)->where(['name' => $data['name'], 'business_id' => $data['business_id'], 'type' => $data['type']])->count() > 0){
            return response()->json(['data' => null, 'message' => "An item of type ".$data['type']." with name ".$data['name']." already exist for the given business"], 400);
        }

        $product->update($data);
        return response()->json(['data' => $product->toArray(), 'message' => "success"], 200);

    }


    // delete product item
    public function delete(Request $request, $id) {
        $product = \App\Models\Product::find($id);

        if($this->user->id != $product->business->user_id and $this->user->type != 'admin'){
            return response()->json(['data'=>null, 'message'=>"Permission denied. You are allowed to delete this business"], 400);
        }

        // delete images if any
        $images = $product->images;
        foreach ($images as $key => $image) {
            # code...
            $path = $image->image_path;
            if(str_contains($path, 'http')){
                $web_root = asset('/');
                $public_path = public_path('/');
                $path = str_ireplace($web_root, $public_path, $path);
            }
            unlink($path);

        }
        $product->delete();

        return response()->json(['data' => [], 'message'=>"success"], 200);

    }


    // search products
    public function search(Request $request) {}


    // get stats
    public function statistics(Request $request) {}
}
