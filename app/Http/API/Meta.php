<?php

namespace App\Http\API;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SqlQueryRunner API',
    contact: new OA\Contact(
    )
)]
#[OA\Server(
    url: 'http://0.0.0.0:80/api',
    description: 'Base server for development'
)]
class Meta
{
    //It's a fake class
}
