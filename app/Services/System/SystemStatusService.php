<?php

namespace App\Services\System;

class SystemStatusService
{
    /**
     * @return array{name: string, environment: string, version: string, status: string}
     */
    public function status(): array
    {
        return [
            'name' => config('app.name'),
            'environment' => app()->environment(),
            'version' => app()->version(),
            'status' => 'ready',
        ];
    }
}
