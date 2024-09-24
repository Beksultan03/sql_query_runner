<?php

namespace App\Http\API\Controllers;

use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class ProductController extends BaseController
{

    private ProductService $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[OA\Get(
        path: '/product/details/{serial_number}',
        operationId: 'details',
        summary: 'Method of getting product details by serial number',
        tags: ['Product'],
        parameters: [
            new OA\Parameter(
                name: 'serial_number',
                description: 'Product\'s serial number',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'SMJ09VRM0'
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/responses/SuccessResponse',
                response: 200,
            ),
            new OA\Response(
                ref: '#/components/responses/InternalServerErrorResponse',
                response: 500,
            ),
        ]
    )]
    public function getProductDetailsBySerialNumber(string $serialNumber): JsonResponse
    {
        try {
            return $this->responseOk(new JsonResource(
                $this->productService->getProductDetailsBySerialNumber($serialNumber)
            ));
        } catch (\Exception $e) {
            return $this->responseError($e->getTrace(), 500);
        }
    }

}
