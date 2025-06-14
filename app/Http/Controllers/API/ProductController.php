<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
    public function show(Request $request, $id) {}


    // create product
    public function create(Request $request) {}


    // update product item
    public function update(Request $request, $id) {}


    // delete product item
    public function delete(Request $request, $id) {}


    // search products
    public function search(Request $request) {}


    // get stats
    public function statistics(Request $request) {}
}
