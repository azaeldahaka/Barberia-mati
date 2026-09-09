<?php

namespace App\Actions\Services;

use App\Models\Service;

class UpdateServiceAction
{
    public function execute(Service $service, array $data): Service
    {
        $service->update([
            'name' => $data['name'],
            'duration_minutes' => $data['duration_minutes'],
            'price' => $data['price'],
        ]);

        return $service;
    }
}
