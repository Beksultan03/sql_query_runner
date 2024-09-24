<?php

use App\Services\Product\ProductService;
use Illuminate\Support\Facades\Route;


Route::prefix('product')->group(callback: function () {
    Route::get('details/{serialNumber}', [ProductService::class, 'getProductDetailsBySerialNumber']);
});
