<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{

    public $user;

    public function __construct(){
        $this->middleware('auth_api');
        $this->user = request()->user('auth_api');

    }

    //list businesses
    /**
     * Get a listing of businesses
     * Possible request params: category_id, user_id, is_approved, page_size
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $businesses = Business::where(['is_approved', 1]);
        if($request->is_approved == 0){
            $businesses = Business::where(['is_approved', 0]);
        }
        if(!empty($request->user_id)){
            $businesses = $businesses->where('user_id', $request->user_id);
        }
        if(!empty($request->category_id)){
            $businesses = $businesses->where('category_id', $request->category_id);
        }
        if(!empty($request->business_id)){
            $businesses = $businesses->where('business_id', $request->business_id);
        }
        $businesses = $businesses->orderBy('id', 'DESC');

        $page_size = 25;
        if(!empty($request->page_size) and $request->page_size > 0){
            $page_size = $request->page_size;
        }

        $businesses = $businesses->paginate($page_size);

        return response()->json(['message' => 'success', 'data' => $businesses->items(), 'previous_page_url' => $businesses->previousPageUrl(), 'next_page_url' => $businesses->nextPageUrl(), 'current_page' => $businesses->currentPage(), 'pages'=>ceil($businesses->total()/intval($page_size))], 200);
    }


    // get business details
    /**
     * Get a single business instance with the given ID
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id){
        $business = Business::find($id);
        if(!empty($business)){
            $business = $business->toArray();
            return response()->json(['data' => $business, 'message' => 'success'], 200);
        }else{
            $business = [];
            return response()->json(['data' => $business, 'message' => 'No business was found with the given id'], 200);
        }
    }


    // create business
    /**
     * Create a business from the post request data
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $request->validate([
            'name' => 'required', 'address' => 'required', 'tel' => 'required',
            'user_id' => 'required', 'category_id' => 'required', 'email' => 'email'
        ]);

        if(Business::where(['tel'=> $request->tel, 'name' => $request->name])->count() < 0){
            $message = "A business with the same name and contact phone number already exists. Consider changing the name or providing a different contact number";
            return response()->json(['data' => null, 'message' => $message], 400);
        }

        $data = [
            'name' => $request->name, 'address' => $request->address, 'tel' => $request->tel, 'user_id' => $request->user_id,
            'category_id' => $request->category_id, 'email' => $request->email, 'is_approved' => request('is_approved', 0), 
            'whatsapp' => $request->whatsapp
        ];


        $business = Business::create($data);
        return response()->json(['data' => $business->toArray(), 'message' => 'success'], 200);
    }


    // update business
    public function update(Request $request, $id){
        if(Business::where(['tel'=> $request->tel, 'name' => $request->name])->whereNot('id', $id)->count() < 0){
            $message = "A business with the same name and contact phone number already exists. Consider changing the name or providing a different contact number";
            return response()->json(['data' => null, 'message' => $message], 400);
        }

        $business = Business::find($id);

        if($this->user->id != $business->user_id and $this->user->type != 'admin'){
            return response()->json(['data' => null, 'message'=>'Permission denied!'], 400);
        }

        if(!empty($business)){
            
            $update = [];
            if(!empty($request->name)){$update['name'] = $request->name;}
            if(!empty($request->tel)){$update['tel'] = $request->tel;}
            if(!empty($request->address)){$update['address'] = $request->address;}
            if(!empty($request->email)){$update['email'] = $request->email;}
            if(!empty($request->is_approved)){$update['is_approved'] = $request->is_approved;}
            if(!empty($request->user_id)){$update['user_id'] = $request->user_id;}
            if(!empty($request->category_id)){$update['category_id'] = $request->category_id;}
            if(!empty($request->whatsapp)){$update['whatsapp'] = $request->whatsapp;}

            $business->update($update);

            return response()->json(['data' => $business->toArray(), 'message' => 'success'], 200);
        }
        return response()->json(['data' => null, 'message' => "No business was found with the specified id"], 200);
    }


    // delete business
    public function delete(Request $request, $id){

        try {
            //code...
            $business = Business::find($id);
    
            if($this->user->id != $business->user_id and $this->user->type != 'admin'){
                return response()->json(['data'=>null, 'message' => "Permission denied!"], 400);
            }
    
            if($business->products->count() > 0){
                return response()->json(['data' => null, 'message' => "Operation failed. Business already has products"], 400);
            }
    
            $data = $business->toArray();
            $business->delete();
            return response()->json(['data' => $data, 'message' => "Business successfully deleted"], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['data' => null, 'message' => "Error occured. ".$th->getMessage()], 500);
        }

    }


    // search business
    public function search(Request $request){
        $search_key = $request->key;
        $page_size = request('page_size', 100);

        $data = \App\Models\Business::join('categories', 'categories.id', '=', 'businesses.category_id')
            ->where('businesses.name', 'LIKE', '%'.$search_key.'%')
            ->orWhere('businesses.address', 'LIKE', '%'.$search_key.'%')
            ->orWhere('businesses.email', 'LIKE', '%'.$search_key.'%')
            ->orWhere('businesses.tel', 'LIKE', '%'.$search_key.'%')
            ->orWhere('categories.name', 'LIKE', '%'.$search_key.'%')
            ->select(['businesses.*'])
            ->union(
                DB::table('businesses')->join('businesses as parents', 'businesses.business_id', '=', 'parents.id')
                    ->join('categories', 'categories.id', '=', 'parents.category_id')
                    ->whereNull('parents.deleted_at')
                    ->where(function($query)use ($search_key){
                        $query->where('parents.name', 'LIKE', '%'.$search_key.'%')
                        ->orWhere('parents.address', 'LIKE', '%'.$search_key.'%')
                        ->orWhere('parents.email', 'LIKE', '%'.$search_key.'%')
                        ->orWhere('parents.tel', 'LIKE', '%'.$search_key.'%')
                        ->orWhere('categories.name', 'LIKE', '%'.$search_key.'%');
                    })
                    ->select(['parents.*'])
            )->paginate($page_size);

            return response()->json(['data' => $data->items(), 'nextPageUrl' => $data->nextPageUrl(), 'previousPageUrl' => $data->previousPageUrl(), 'current_page' => $data->currentPage(), 'pages' => $data->total()/$page_size, 'message' => "success"], 200);
        
    }


    // business statistics
    public function statistics(Request $request)  {}

}
