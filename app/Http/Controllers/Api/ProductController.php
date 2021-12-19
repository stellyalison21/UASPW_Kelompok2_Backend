<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Product;

class ProductController extends Controller
{
    public function index() {
        $products = Product::all();
        if (count($products)>0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $products
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function show($id) {
        $product = Product::find($id);
        if (!is_null($product)>0) {
            return response([
                'message' => 'Retrieve Product Success',
                'data' => $product
            ], 200);
        }

        return response([
            'message' => 'Product Not Found',
            'data' => null
        ], 404);
    }

    public function store(Request $request) {
        $storeData = $request->all();
        $validate = Validator::make($storeData,[
            'product_name' => 'required|max:50|unique:products',
            'desc' => 'required|max:255',
            'stock' => 'required|numeric',
            'price' => 'required|numeric'
        ]);

        if($validate->fails())
            return response(['message'=> $validate->errors()],400);
        if($files = $request->file('product_img')){
            $imageName = $files->getClientOriginalName();
            $request->product_img->move(public_path('images'),$imageName);
            $product = Product::create([
                'product_name'=>$request->product_name,
                'product_img'=>'/images/'.$imageName,
                'desc'=>$request->desc,
                'stock'=>$request->stock,
                'price'=>$request->price,
            ]);
            return response([
                'message'=>'Add Product Success',
                'data'=>$product
            ], 200);
        }
    }

    public function destroy($id) {
        $product = Product::find($id);
        if(is_null($product)) {
            return response([
                'message' => 'Product Not Found',
                'data' => null
            ], 404);
        }

        if($product->delete()) {
            return response([
                'message' => 'Delete Product Success',
                'data' => $product
            ], 200);
        }

        return response([
            'message' => 'Delete Product Failed',
            'data' => null,
        ], 400);
    }

    public function update(Request $request, $id) {
        $product = Product::find($id);
        if(is_null($product)) {
            return response([
                'message' => 'Product Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'product_name'=>['max:60', 'required', Rule::unique('products')->ignore($product)],
            'desc' => 'required|max:255',
            'stock' =>'required|numeric',
            'price'=>'required|numeric'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $product->product_name = $updateData['product_name'];
        $product->desc = $updateData['desc'];
        $product->stock = $updateData['stock'];
        $product->price = $updateData['price'];

        if($product->save()) {
            return response([
                'message' => 'Update Product Success',
                'data' => $product
            ], 200);
        }
        return response([
            'message' => 'Update Product Failed',
            'data' => null,
        ], 400);
    }

    public function uploadPicture(Request $request, $id){
        $product = Product::find($id);
        if(is_null($product)){
            return response([
                'message' => 'Product Not found',
                'data' => null
            ],404);
        }

        if(!$request->hasFile('product_img')) {
            return response([
                'message' => 'Upload Picture Failed',
                'data' => null,
            ],400);
        }
        $file = $request->file('product_img');

        if(!$file->isValid()) {
            return response([
                'message'=> 'Upload Picture Failed',
                'data'=> null,
            ],400);
        }

        $image = public_path().'/images/';
        $file -> move($image, $file->getClientOriginalName());
        $image = '/images/'.$file->getClientOriginalName();
        $updateData = $request->all();
        Validator::make($updateData, [
            'product_img' => $image
        ]);
        $product->product_img = $image;

        if($product->save()){
            return response([
                'message' => 'Upload Picture Success',
                'path' => $image,
            ],200);
        }

        return response([
            'messsage'=>'Upload Picture Failed',
            'data'=>null,
        ],400);
    }
}
