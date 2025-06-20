<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductRecordModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    //

    protected function list(){
        return response()->json(ProductModel::all());
    } 
    public function category()
    {
        // return $this->belongsTo(CategoryModel::class);

        try{
            $product = ProductModel::all();
            $product = ProductModel::with('category')->find($product->id);
            return response()->json([
                'message' => 'Success',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'current_qty' => $product->current_qty,
                    'category' => $product->category ? $product->category->name : null,
                    'description' => $product->description,
                    'image' => $product->image,
        ]
            ],201);
        }
        
        catch(\Exception $e){
            Log::error('Error get product: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something when wrong.',
                'error' => $e->getMessage()
            ],500);
        }
        
    }


    protected function add(Request $request){
        $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|numeric',
        'current_qty' => 'required|numeric',
        'discount' => 'nullable|numeric',
         'category_id' => 'nullable|integer|exists:categories,id',
        'desription' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);

        try{
            $imagePath = null;
            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('products','public');
            }

            $product = ProductModel::create([
                'name' => $request->name,
                'price' => $request->price,
                'current_qty' => $request->current_qty,
                'discount' => $request->discount,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'image' => $imagePath,

            ]);

                // Store initial product record
            ProductRecordModel::create([
                'product_id' => $product->id,
                'old_name' => null,
                'new_name' => $product->name,
                'old_price' => null,
                'new_price' => $product->price,
                'old_qty' => null,
                'new_qty' => $product->current_qty,
                'updated_by' => Auth::id()
            ]);
            $product = ProductModel::with('category')->find($product->id);
            return response()->json([
                'message' => 'Product Added Successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'current_qty' => $product->current_qty,
                    'category' => $product->category ? $product->category->name : null,
                    'description' => $product->description,
                    'image' => $product->image,
        ]
            ],201);
        }
        catch(\Exception $e){
            Log::error('Error adding product: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something when wrong.',
                'error' => $e->getMessage()
            ],500);
        }
    }

    protected function update(Request $request,$id){
         $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|numeric',
        'current_qty' => 'required|numeric',
        'discount' => 'nullable|numeric',
        'category_id' => 'nullable|integer|exists:categories,id',
        'desription' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);
        try{
            $product = ProductModel::findOrFail($id);

            $oldName = $product->name;
            $oldPrice = $product->price;
            $oldQty = $product->current_qty;

            // Handle image update
            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('products','public');
                $product->image = $imagePath;
            }
            $product->name = $request->name;
            $product->price = $request->price;
            $product->discount = $request->discount;
            $product->current_qty = $request->current_qty;
            $product->category_id = $request->category_id;
            $product->description = $request->description;
            $product->save();

             // If name or price changed, store to product_records
            if ($oldName !== $product->name || $oldPrice != $product->price || $oldQty != $product->current_qty) {
                ProductRecordModel::create([
                    'product_id' => $product->id,
                    'old_name' => $oldName !== $product->name ? $oldName : null,
                    'new_name' => $oldName !== $product->name ? $product->name : null,
                    'old_price' => $oldPrice != $product->price ? $oldPrice : null,
                    'new_price' => $oldPrice != $product->price ? $product->price : null,
                    'old_qty' => $oldQty != $product->current_qty ? $oldQty : null,
                    'new_price' => $oldQty != $product->current_qty ? $product->oldQty : null,
                    'updated_by' => Auth::id()
                ]);
            }

            return response()->json([
                'message' => 'Product update successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'current_qty' => $product->current_qty,
                    'category' => $product->category ? $product->category->name : null,
                    'description' => $product->description,
                    'image' => $product->image,
                ]
                ],200);
        }
        catch(\Exception $e){
             return response()->json([
            'message' => 'Update failed.',
            'error' => $e->getMessage()
        ], 500);
        }
    }

    protected function delete($id){
        try{
            $product = ProductModel::findOrFail($id);
            $product->delete();
            return response()->json([
                'message' => 'Product delete successfully'
            ],200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Delete failed.',
                'error' => $e->getMessage()
            ],500);
        }
    }


}
