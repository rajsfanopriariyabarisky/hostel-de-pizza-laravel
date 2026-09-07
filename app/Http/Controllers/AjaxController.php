<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderList;
use Illuminate\Http\Request;
use App\Http\Requests\addToCartRequest;

class AjaxController extends Controller
{
    // add to cart
    public function addToCart(addToCartRequest $request){
        Cart::create($request->validated());
        $response = [
            'status'=>'success',
            'message'=>'insert to cart success',
        ];
        return response()->json($response,200);
    }

    // qty change
    public function qtyChange(Request $request){
        Cart::where('id',$request->id)
        ->update([
            'qty'=>$request->qty,
            'user_id'=>auth()->id(),
        ]);
    }

    // remove cart product
    public function remove(Request $request){
        Cart::where('id',$request->id)->delete();
    }

    // add to order
    public function orderProcess(Request $request){
        $total = 0;
        foreach ($request->all() as $item) {
            $data = OrderList::create([
                'user_id' => $item['user_id'],
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'total' => $item['total'], // pastikan nilai total sudah dalam idr (Rp)
                'order_code' => $item['order_code'],
            ]);
            $total += $item['total'];
        }
        Cart::where('user_id',$data->user_id)->delete();
        Order::create([
            'user_id' => auth()->id(),
            'order_code' => $data->order_code,
            'total' => $total + 8000, // mengganti biaya tambahan ke idr (Rp8000)
        ]);
        $response = [
            'status'=>'success',
        ];
        return response()->json($response,200);
    }

    // remove all products from cart
    public function clearCart(){
        Cart::where('user_id',auth()->id())->delete();
    }

    // add view count
    public function viewCount(Request $request){
        $data = Product::where('id',$request->product_id)->first();
        Product::where('id',$request->product_id)->update(['view_count'=>$data->view_count+1]);

    }

    // home page sorting with price
    public function sortPrice(Request $request){
        // Mengubah nilai harga agar tetap dalam IDR
        $data = Product::whereBetween('price',[$request->val1,$request->val2]) // pastikan input filter dalam idr (Rp)
                ->orderBy('id','desc')
                ->get();
        return response()->json($data,200);
    }
}
