<?php

namespace App\Services\SerialNumber;

use Illuminate\Support\Facades\DB;

class SerialNumberService
{
    public function getOrderId(string $serialNumber): ?object
    {
        $serialHistory = DB::table("tbl_sb_serial_numbers as serial_num")
            ->leftJoin('tbl_sb_serial_number_location_change_history as history', 'history.serial_id', '=', 'serial_num.serial_id')
            ->where('serial_number', $serialNumber)
            ->where('history.shipped_location_order_id','!=', 0)
            ->whereNotNull('history.shipped_location_order_id')
            ->select('history.shipped_location_order_id as order_id')
            ->first();

        return $serialHistory ?? null;
    }

    public function getSerialNumberValue(string $serialNumber)
    {
        return DB::table("tbl_sb_serial_numbers")
            ->where('serial_number', $serialNumber)
            ->select('serial_id', 'product_id')
            ->first();
    }

}
