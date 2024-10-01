<?php

namespace App\Services\Product;

use App\Services\Orders\TechnicianOrderService;
use App\Services\SerialNumber\SerialNumberService;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private TechnicianOrderService  $technicianOrderService;
    private SerialNumberService  $serialNumberService;
    public function __construct(TechnicianOrderService $technicianOrderService, SerialNumberService $serialNumberService)
    {
        $this->technicianOrderService = $technicianOrderService;
        $this->serialNumberService = $serialNumberService;
    }

    public function getProductDetailsBySerialNumber(string $serialNumber): array
    {
        $serialNumberValue = $this->serialNumberService->getSerialNumberValue($serialNumber);
        if (!$serialNumberValue) {
            return [];
        }

        $orderId = $this->getOrderIdBySerialNumber($serialNumber);

        $sku = $this->getOrderSku($orderId);

        $result = [
            'serial_number' => $serialNumber,
            'order_id' => $orderId,
            'raid' => $this->getRaidFromOrder($orderId)
        ];

        $productDetails = $this->getProductDetails($sku, $serialNumberValue);

        return array_merge($result, $productDetails);
    }

    private function getOrderIdBySerialNumber(string $serialNumber): ?string
    {
        return $this->technicianOrderService->getOrderId($serialNumber)
            ?: $this->serialNumberService->getOrderId($serialNumber);
    }

    private function getOrderSku(?string $orderId): ?string
    {
        if (!$orderId) {
            return null;
        }

        $order = DB::table('tbl_sb_order_items as item')
            ->select('item.productid as sku')
            ->where('item.orderid', $orderId)
            ->first();

        if (!$order) {
            $order = DB::table('tbl_sb_history_order_item as item')
                ->select('item.sku as sku')
                ->where('item.orderid', $orderId)
                ->first();
        }

        return $order ? $order->sku : null;
    }

    private function getRaidFromOrder(?int $orderId): ?string
    {
        return $orderId ? optional($this->getRaidData($orderId))->Raid : null;
    }

    private function getRaidData(string $orderId): ?object
    {
        return DB::table('tbl_sb_qc_data')->where('orderid', $orderId)->select('Raid')->first();
    }

    private function getProductDetails(?string $sku, object $serialNumberValue): array
    {
        if (!$sku) {
            return $this->getBaseProductDetailsById($serialNumberValue->product_id);
        }
        $kitData = $this->getDetailsFromKit($sku);

        return $kitData ?? $this->getBaseProductDetailsById($serialNumberValue->product_id);
    }

    private function getBaseProductDetailsById(int $productId): array
    {
        $baseProduct = DB::table('tbl_base_product')
            ->where('id', $productId)
            ->select('system_title', 'display_title')
            ->first();

        return $baseProduct ? $this->extractProductDetails($baseProduct) : [];
    }

    private function getDetailsFromKit(string $sku): ?array
    {
        $normalizedSku = $this->normalizeSku($sku);
        return $this->getKitData($normalizedSku);
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
            ?: DB::table('tbl_kit_archive')
                ->where('kit_sku', $sku)
                ->select('kit_display_title', 'kit_ram_title', 'kit_storage_title', 'kit_gpu_title', 'kit_os_title', 'kit_cpu_title')
                ->first();

        return $kitValue ? $this->mapKitData($kitValue) : null;
    }

    private function mapKitData(object $kitValue): array
    {
        return [
            'display_title' => $kitValue->kit_display_title,
            'ram' => $kitValue->kit_ram_title,
            'storage' => $kitValue->kit_storage_title,
            'gpu' => $kitValue->kit_gpu_title,
            'os' => $kitValue->kit_os_title,
            'cpu' => $kitValue->kit_cpu_title,
        ];
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
