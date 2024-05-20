<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'order_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->order_value = $request->input('order_value');
        $order->save();

        $order_id = $order->id;
        $process_id = rand(1, 10);
        $order_date = Carbon::now()->toDateTimeString();

        $orderData = [
            'Order_ID' => $order_id,
            'Customer_Name' => $order->customer_name,
            'Order_Value' => $order->order_value,
            'Order_Date' => $order_date,
            'Order_Status' => 'Processing',
            'Process_ID' => $process_id,
        ];

        try {
            $response = Http::post('https://wibip.free.beeceptor.com/order', $orderData);
            if ($response->successful()) {
                $status = 'success';
            } else {
                $status = 'failed';
            }
        } catch (\Exception $e) {
            $status = 'failed';
            \Log::error('Failed to send order to third-party API', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'order_id' => $order_id,
            'process_id' => $process_id,
            'status' => $status,
        ], 201);
    }
}
