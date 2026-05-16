<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemStatusResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'],
            'environment' => $this->resource['environment'],
            'version' => $this->resource['version'],
            'status' => $this->resource['status'],
        ];
    }
}
