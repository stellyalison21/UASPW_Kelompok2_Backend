<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use DB;
use App\Models\PlaceOrder;
use App\Models\Product;

class PlaceOrderController extends Controller
{
    public function index() {
        $place_orders = PlaceOrder::all();
        if (count($place_orders)>0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $place_orders
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function show($id_user) {
        $place_orders = PlaceOrder::find($id_user);
        if (!is_null($place_orders)>0) {
            return response([
                'message' => 'Retrieve Order Success',
                'data' => $place_orders
            ], 200);
        }

        return response([
            'message' => 'Order Not Found',
            'data' => null
        ], 404);
    }

    public function showUserOrder($id_user){
        $place_orders = DB::table('place_orders')->where('id_user', $id_user)->get();
        if(!is_null($place_orders)){
            return response([
                'message' => 'Retrieve Order Success',
                'data' => $place_orders
            ], 200);
        }

        return response([
            'message' => 'Order Not Found',
            'data' => null
        ], 404);
    }

    public function store(Request $request) {
        $storeData = $request->all();
        $id_product = $storeData['id_product'];
        $id_user = $storeData['id_user'];

        $product = Product::where('id', $id_product)->first();
        if($storeData['quantity'] > $product['stock']){
            return response([
                'message' => 'Out of Stock',
                'data' => null,
            ],200);
        }

        $place_orders = PlaceOrder::where([
            ['id_user', $id_user],
            ['id_product', $id_product]
        ])->first();
        if($place_orders != null){
            return $this->update($request,$id_user);
        }

        $validate = Validator::make($storeData, [
            'id_product'=>'required',
            'id_user'=>'required',
            'product_name'=>'required',
            'price'=>'required',
            'quantity'=>'required'
        ]);

        $product['stock'] = $product['stock'] - $storeData['quantity'];
        $product->save();
        $storeData['total'] = $storeData['price'] * $storeData['quantity'];

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $place_orders = PlaceOrder::create($storeData);
        return response([
            'message' => 'Add Order Success',
            'data' => $place_orders
        ], 200);
    }

    public function destroy($id) {
        $place_orders = PlaceOrder::where('id', $id)->first();
        $id_product = $place_orders['id_product'];
        $product = Product::where('id', $id_product)->first();

        if(is_null($place_orders)) {
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 404);
        }else{
            $product['stock'] = $product['stock'] + $place_orders['quantity'];
            $product->save();
        }

        if($place_orders->delete()) {
            return response([
                'message' => 'Delete Your Order Success',
                'data' => $place_orders,
            ], 200);
        }

        return response([
            'message' => 'Delete Your Order Failed',
            'data' => null,
        ], 400);
    }

    public function update(Request $request, $id) {
        $storeData = $request->all();
        $id_user = $storeData['id_user'];
        $id_product = $storeData['id_product'];

        $product = Product::where('id', $id_product)->first();
        
        if($storeData['quantity'] > $product['stock']){
            return response([
                'message' => 'Out of Stock',
                'data' => null,
            ],200);
        }else{
            $product['stock'] = $product['stock'] - $storeData['quantity'];
            $product->save();
        }
        $place_orders = PlaceOrder::where([
            ['id_user', $id_user],
            ['id_product', $id_product]
        ])->first();

        if(is_null($place_orders)) {
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 404);
        }

        $validate = Validator::make($storeData, [
            'id_product'=>'required',
            'id_user'=>'required',
            'product_name'=>'required',
            'price'=>'required',
            'quantity'=>'required'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $quantity1 = $place_orders['quantity'] + $storeData['quantity'];
        $totalPrice = $quantity1 * $place_orders['price'];

        $place_orders['product_name'] = $storeData['product_name'];
        $place_orders['price'] = $storeData['price'];
        $place_orders['quantity'] = $quantity1;
        $place_orders['total'] = $totalPrice;

        if($place_orders->save()) {
            return response([
                'message' => 'Your Order Has Been Updated',
                'data' => $place_orders,
            ], 200);
        }
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ], 400);
    }

    public function updateCart(Request $request, $id) {
        $place_orders = Order::find($id);
        if(is_null($place_orders)){
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 404);
        }

        $storeData = $request->all();
        $product = Product::where('id', $id_product)->first();
            if($storeData['quantity'] > $product['stock']){
                return response([
                    'message' => 'Out of Stock',
                    'data' => null,
                ], 200);
            }
    
        $validate = Validator::make($storeData,[
            'id_product'=>'required',
            'id_user'=>'required',
            'product_name'=>'required',
            'price'=>'required',
            'quantity'=>'required'
        ]);

        if($validate->fails())
            return response(['message'=>$validate->errors()], 400);

        $quantity1 = $place_orders['quantity'] + $storeData['quantity'];
        $totalPrice = $quantity1 * $place_orders['price'];

        $place_orders['product_name'] = $storeData['product_name'];
        $place_orders['price'] = $storeData['price'];
        $place_orders['quantity'] = $quantity1;
        $place_orders['total'] = $totalPrice;

        if($place_orders->save()){
            return response([
                'message'=>'Your Chart Have Been Updated',
                'data'=>$place_orders,
            ], 200);
        }
        return response([
            'message'=>'Update Chart Failed ',
            'data'=>$place_orders,
        ], 404);
    }
}
