<?php

namespace App\Services\Product;

use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getProductDetailsBySerialNumber(string $serialNumber): array
    {
        $serialNumberValue = $this->getSerialNumberValue($serialNumber);

        if (!$serialNumberValue) {
            return [];
        }

        $order = $this->getOrderDetails($serialNumberValue->serial_id);

        $result = [
            'serial_number' => $serialNumber,
            'raid' => $this->getRaidFromOrder($order)
        ];

        $productDetails = $order ? $this->getKitOrProductDetails($order->sku, $serialNumberValue->product_id)
            : $this->getBaseProductDetailsById($serialNumberValue->product_id);

        return array_merge($result, $productDetails);
    }

    private function getSerialNumberValue(string $serialNumber)
    {
        return DB::table("tbl_sb_serial_numbers")
            ->where('serial_number', $serialNumber)
            ->select('serial_id', 'product_id')
            ->first();
    }

    private function getOrderDetails(string $serialId)
    {
        $order = DB::table('tbl_sb_serial_number_location_change_history as history')
            ->leftJoin('tbl_sb_order_items as item', 'item.orderid', '=', 'history.shipped_location_order_id')
            ->select('item.productid as sku', 'item.orderid as order_id')
            ->where('history.serial_id', $serialId)
            ->where('history.shipped_location_order_id', '!=', 0)
            ->whereNotNull('item.id')
            ->first();

        return $order ?: DB::table('tbl_sb_serial_number_location_change_history as history')
            ->leftJoin('tbl_sb_history_order_item as item', 'item.orderid', '=', 'history.shipped_location_order_id')
            ->select('item.sku as sku', 'item.orderid as order_id')
            ->where('history.serial_id', $serialId)
            ->where('history.shipped_location_order_id', '!=', 0)
            ->whereNotNull('item.id')
            ->first();
    }

    private function getRaidFromOrder($order)
    {
        return $order ? $this->getRaidData($order->order_id)?->Raid : null;
    }

    private function getRaidData(string $orderId)
    {
        return DB::table('tbl_sb_qc_data')->where('orderid', $orderId)->select('Raid')->first();
    }

    private function getBaseProductDetailsById(int $productId): array
    {
        $baseProduct = DB::table('tbl_base_product')
            ->where('id', $productId)
            ->select('system_title', 'display_title')
            ->first();

        return $this->extractProductDetails($baseProduct);
    }

    private function getKitOrProductDetails(string $sku, int $productId): array
    {
        $sku = $this->normalizeSku($sku);
        $kitData = $this->getKitData($sku);

        return $kitData ?: $this->getBaseProductDetailsById($productId);
    }

    private function normalizeSku(string $sku): string
    {
        if (str_starts_with($sku, 'B')) {
            $bundleSku = DB::table('tbl_kit_bundle_relation')
                ->select('kit_sku')
                ->where('sc_bundle_kit_sku', $sku)
                ->orWhere('bundle_kit_sku', $sku)
                ->first();

            return $bundleSku ? str_replace(['-GPT', '-CA'], '', $bundleSku->kit_sku) : $sku;
        }

        return str_replace('-GPT', '', $sku);
    }

    private function getKitData(string $sku): ?array
    {
        $kitValue = DB::table('tbl_kit')
            ->where('kit_sku', $sku)
            ->select('kit_display_title', 'kit_ram_title', 'kit_storage_title', 'kit_gpu_title', 'kit_os_title', 'kit_cpu_title')
            ->first()
            ?? DB::table('tbl_kit_archive')
                ->where('kit_sku', $sku)
                ->select('kit_display_title', 'kit_ram_title', 'kit_storage_title', 'kit_gpu_title', 'kit_os_title', 'kit_cpu_title')
                ->first();

        return $kitValue ? [
            'display_title' => $kitValue->kit_display_title,
            'ram' => $kitValue->kit_ram_title,
            'storage' => $kitValue->kit_storage_title,
            'gpu' => $kitValue->kit_gpu_title,
            'os' => $kitValue->kit_os_title,
            'cpu' => $kitValue->kit_cpu_title,
        ] : null;
    }

    private function extractProductDetails(object $productDetails): array
    {
        $productValues = explode(':||:', $productDetails->system_title);

        return [
            'display_title' => $productDetails->display_title,
            'ram' => $productValues[2] ?? null,
            'storage' => $productValues[3] ?? null,
            'gpu' => $productValues[5] ?? null,
            'os' => $productValues[6] ?? null,
            'cpu' => $productValues[1] ?? null,
        ];
    }
}
