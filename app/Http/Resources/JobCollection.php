<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class JobCollection extends ResourceCollection
{


    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Jobs retrieved successfully',
        ];
    }
}