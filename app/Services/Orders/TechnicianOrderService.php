<?php

namespace App\Services\Orders;

use Illuminate\Support\Facades\DB;

class TechnicianOrderService
{
    public function getOrderId(string $serialNumber): ?string
    {
        $order = DB::table("tbl_sb_technician_orders")
            ->where('serial_number', $serialNumber)
            ->select('order_id')
            ->first();

        return $order ? $order->order_id : null;
    }

}
