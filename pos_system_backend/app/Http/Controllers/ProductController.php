<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductRecordModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    //

    protected function list(){
        try{
             $product = ProductModel::all();
            if (!empty($product)) {
            return response()->json([
            'message' => 'Products retrieved successfully.',
            'data' => $product
        ], 200);
        }
        else{
            return response()->json([
                'message' => 'No data found '
            ], 404);
        }
        }
        catch(\Exception $e){
            Log::error('Error get product: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
        }
    } 

    protected function getById($id){
          try{
            $product = ProductModel::findOrFail($id);
            if(!empty($product)){
                return response()->json([
                'message' => 'Products retrieved successfully.',
                'data' => $product
            ], 200);
            }
          else{
               return response()->json([
                'message' => 'No data found '
            ], 404);
          }
        }
        catch(\Exception $e){
            Log::error('Error get product by id: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        
        ], 500); // 500 Internal Server Error
        }
        
    }

    public function category()
    {
        // return $this->belongsTo(CategoryModel::class);

        try{
            $product = ProductModel::all();
            $product = ProductModel::with('category')->find($product->id);
            if(!empty($product)){
                 return response()->json([
                'message' => 'Success',
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
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
           else{
               return response()->json([
                'message' => 'No data found '
            ], 404);
           }
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
        'code' => 'nullable|string|max:255',
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|numeric',
        'current_qty' => 'required|numeric',
        'discount' => 'nullable|numeric',
        'price_after_discount' => 'nullable|numeric',
        'category_id' => 'nullable|integer|exists:categories,id',
        'desription' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);

        try{
            $imagePath = null;
            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('products','public');
            }
            $discount = (float) ($request->discount ?? 0);
            $price = (float) $request->price;
            $priceAfterDiscount = round($price - ($price * $discount / 100), 2);


            $product = ProductModel::create([
                'code' => $request->code,
                'name' => $request->name,
                'price' => $request->price,
                'current_qty' => $request->current_qty,
                'discount' => $request->discount,
                'price_after_discount' => $priceAfterDiscount,
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
                'old_discount' => null,
                'new_discount' => $product->discount,
                'old_code' => null,
                'new_code' => $product->code,
                'updated_by' => Auth::id()
            ]);
            $product = ProductModel::with('category')->find($product->id);
            
            if(!empty($product)){
                return response()->json([
                'message' => 'Product Added Successfully',
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'price_after_discount' => $priceAfterDiscount,
                    'current_qty' => $product->current_qty,
                    'category' => $product->category ? $product->category->name : null,
                    'description' => $product->description,
                    'image' => $product->image,
                ]
                ],201);
            }
            else{
                return response()->json([
                'message' => 'Add failed '
            ], 500);
            }
        }
        catch(\Exception $e){
            Log::error('Error adding product: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something when wrong.',
                'error' => $e->getMessage()
            ],500);
        }
    }

    protected function update(Request $request, $id)
    {
        $product = ProductModel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'current_qty' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'price_after_discount' => 'nullable|numeric',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
        ]);

        $validator = Validator::make($request->only('code'), [
            'code' => ['required', 'string', Rule::unique('products', 'code')->ignore($product->id)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldName = $product->name;
            $oldPrice = $product->price;
            $oldQty = $product->current_qty;
            $oldDiscount = $product->discount;
            $oldCode = $product->code;

            $discount = (float) ($request->discount ?? 0);
            $price = (float) $request->price;
            $priceAfterDiscount = round($price - ($price * $discount / 100), 2);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image = $imagePath;
            }
            
            $product->code = $request->code;
            $product->name = $request->name;
            $product->price = $price;
            $product->discount = $discount;
            $product->current_qty = $request->current_qty;
            $product->price_after_discount = $priceAfterDiscount;
            $product->category_id = $request->category_id;
            $product->description = $request->description;
            $product->save();

            if ($oldCode !== $product->code || $oldName !== $product->name || $oldPrice != $product->price || $oldQty != $product->current_qty || $oldDiscount != $product->discount) {
                ProductRecordModel::create([
                    'product_id' => $product->id,
                    'old_code' => $oldCode !== $product->code ? $oldCode : null,
                    'new_code' => $oldCode !== $product->code ? $product->code : null,
                    'old_name' => $oldName !== $product->name ? $oldName : null,
                    'new_name' => $oldName !== $product->name ? $product->name : null,
                    'old_price' => $oldPrice != $product->price ? $oldPrice : null,
                    'new_price' => $oldPrice != $product->price ? $product->price : null,
                    'old_qty' => $oldQty != $product->current_qty ? $oldQty : null,
                    'new_qty' => $oldQty != $product->current_qty ? $product->current_qty : null,
                    'old_discount' => $oldDiscount != $product->discount ? $oldDiscount : null,
                    'new_discount' => $oldDiscount != $product->discount ? $product->discount : null,
                    'updated_by' => Auth::id()
                ]);
            }

            if(!empty($product)){
                return response()->json([
                'message' => 'Product updated successfully',
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'current_qty' => $product->current_qty,
                    'price_after_discount' => $product->price_after_discount,
                    'category' => $product->category ? $product->category->name : null,
                    'description' => $product->description,
                    'image' => $product->image,
                ]
            ], 200);
            }
            else{
                return response()->json([
                'message' => 'Update failed '
            ], 500);
            }
          
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product update failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function delete($id)
    {
        try {
            $product = ProductModel::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
