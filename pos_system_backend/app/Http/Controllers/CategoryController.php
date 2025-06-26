<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    //

     public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function list(){
        try{
            $category = CategoryModel::all();
            if(!empty($category)){
                return response()->json([
                    'category' => $category
                ],200);
            }
            else{
                 return response()->json([
                'message' => 'No data found '
            ], 404);
            }
          
        }
        catch(\Throwable $e){
            Log::error('Error get category: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
        }

        
    }

    public function getById($id){
        try{
            $category = CategoryModel::findOrFail($id);
        if(!empty($category)){
                return response()->json([
                    'category' => $category
                ],200);
            }
            else{
                 return response()->json([
                'message' => 'No data found '
            ], 404);
            }
        }
        catch(\Throwable $e){
            Log::error('Error get category by id: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
        }
       
    }

    public function add(Request $request){
    $request->validate([
        'name' => 'required|string|max:255',
        'note' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);
    try {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public'); // stores in storage/app/public/categories
        }

        $category = CategoryModel::create([
            'name' => $request->name,
            'note' => $request->note,
            'image' => $imagePath, 
        ]);

        if(!empty($category)){
            return response()->json([
            'message' => 'Category added successfully.',
            'category' => $category
        ], 201); // 201 Created
        }
        else{
             return response()->json([
                'message' => 'Add failed '
            ], 500);
        }
        

    } catch (\Throwable $e) {
        // Optional: log the error for debugging
        Log::error('Error adding category: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
    }
    
}

    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'note' => 'nullable|text',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);

    try {
        $category = CategoryModel::findOrFail($id);

        // Handle image update
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->image = $imagePath;
        }

        // Update name and note
        $category->name = $request->name;
        $category->note = $request->note;
        $category->save();

        if(!empty($category)){
            return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ], 200);
        }
        else{
             return response()->json([
                'message' => 'Update failed '
            ], 500);
        }
  

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Update failed.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function delete($id)
    {
        try {
            $category = CategoryModel::findOrFail($id);
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Delete failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
