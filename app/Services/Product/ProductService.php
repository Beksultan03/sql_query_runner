<?php

namespace App\Services\Product;

use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getProductDetailsBySerialNumber(string $serialNumber): array
    {
        return DB::table('tbl_base_product as p')
            ->leftJoin('tbl_master_data_values as cpu', 'p.cpu_id', '=', 'cpu.value_id')
            ->leftJoin('tbl_master_data_values as ssd', 'p.ssd_id', '=', 'ssd.value_id')
            ->leftJoin('tbl_master_data_values as hdd', 'p.ssd_id', '=', 'hdd.value_id')
            ->leftJoin('tbl_master_data_values as ram', 'p.ram_id', '=', 'ram.value_id')
            ->leftJoin('tbl_master_data_values as os', 'p.os_id', '=', 'os.value_id')
            ->leftJoin('tbl_sb_serial_numbers as sn', 'p.id', '=', 'sn.product_id')
            ->select(
                'p.display_title',
                'sn.serial_number',
                'cpu.name as cpu_name',
                'ssd.name as ssd_name',
                'hdd.name as hdd_name',
                'ram.name as ram_name',
                'os.name as os_name'
            )
            ->where('sn.serial_number', $serialNumber)
            ->get()
            ->toArray();
    }
}
