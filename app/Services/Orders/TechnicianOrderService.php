<?php

namespace App\Services\Orders;

use Illuminate\Support\Facades\DB;

class TechnicianOrderService
{
    public function getOrderId(string $serialNumber): ?object
    {
        $order = DB::table("tbl_sb_technician_orders")
            ->where('serial_number', $serialNumber)
            ->select('order_id', 'is_PU_order')
            ->first();
        return $order ?? null;
    }

}
