<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
use App\Models\Transaction;
use App\Models\PlaceOrder;

class TransactionController extends Controller
{
    public function index() {
        $transactions = Transaction::all();
        if (count($transactions)>0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $transactions
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function store(Request $request) {
        $storeData = $request->all();
        $place_orders = PlaceOrder::where(
            'id_user', 
            $storeData['id_user'],
        )->get();

        if($place_orders){
            foreach($place_orders as $order) {
                $temp = Transaction::where('id_product', $order->id_product)->first();
           
                if($temp!=null){
                    $temp->sold_items = $order->quantity+$temp['sold_items'];
                    $temp->total = $order->total+$temp['total'];
                    $temp->save();
                }else{
                    Transaction::create([
                        'id_product' => $order->id_product,
                        'product_name' => $order->product_name,
                        'sold_items' => $order->quantity,
                        'total' => $order->total
                    ]);
                }
                $order->delete();
            }
            return response([
                'message' => 'Add Transaction Success',
            ],200);
        }
        return response([
            'message' => 'Add Transaction Failed',
        ],200);
    }
}
