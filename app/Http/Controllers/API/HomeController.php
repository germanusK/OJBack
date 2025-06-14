<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    // get categories
    public function categories(Request $request) {
        try {
            //code...
            $data = \App\Models\Category::orderBy('name')->get();
            return response()->json(['data' => $data->toArray(), 'message' => "success"], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['data' => null, 'message' => $th->getMessage()], 500);
        }
    }


    // create category
    public function create_category(Request $request){
        try {
            //code...
            $request->validate(['name' =>'required']);

            $data = [
                'name' =>$request->name, 'description' => $request->description
            ];

            \App\Models\Category::updateOrInsert(['name' => $request->name], $data);
            $category = \App\Models\Category::where(['name' => $request->name])->first();

            return response()->json(['data' => $category?->toArray(), 'message' => "success"], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['data' => null, 'messgae' => "success"], 500);
        }
    }


    // update category item
    public function update_category($id, Request $request) {
        try {
            //code...
            DB::beginTransaction();
            $category = \App\Models\Category::find($id);
            if($category != null){
                $data =['name' => $request->name, 'description' => $request->description];
                $category->update($data);
                DB::commit();
                return response()->json(['data' => $category->toArray(), 'message' => "success"], 200);
            }
            return response()->json(['data' => null, 'message' => "No category was found with specified ID"], 400);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json(['data' => null, 'message' => "Error occured. ".$th->getMessage()], 500);
        }
    }


    // delete category
    public function delete_category(Request $request, $id) {

        try {
            //code...
            $category = \App\Models\Category::find($id);
    
            if($category == null){
                return response()->json(['data' => null, 'message' => "No category found with given ID"], 400);
            }
    
            if($category->businesses()->count() > 0 || $category->products()->count() > 0){
                return response()->json(['data' => null, 'message' => "Operation failed. Category already has a business or product"], 400);
            }
    
            $data = $category->toArray();
            $category->delete();
            return response()->json(['data' => $data, 'message' => "success"], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['data' => null, 'message' => "Error occured. ".$th->getMessage()], 500);
        }
    }


    // summary statistics
    public function summary_statistics(Request $request){}


    // summary statistics
    public function search(Request $request){}
}
