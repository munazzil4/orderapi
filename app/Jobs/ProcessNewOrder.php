<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ProcessNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        $process_id = rand(1, 10);
        $order_date = Carbon::now()->toDateTimeString();

        $orderData = [
            'Order_ID' => $order->id,
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
    }
}
