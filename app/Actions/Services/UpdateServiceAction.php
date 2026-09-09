<?php

namespace App\Actions\Services;

use App\Models\Service;

class UpdateServiceAction
{
    public function execute(Service $service, array $data): Service
    {
        $service->itemCatalogo->update([
            'nombre' => $data['name'],
            'precio' => $data['price'],
            'duracion_minutos' => $data['duration_minutes'],
        ]);

        if (array_key_exists('cuenta_para_fidelizacion', $data)) {
            $service->update([
                'cuenta_para_fidelizacion' => $data['cuenta_para_fidelizacion'],
            ]);
        }

        return $service;
    }
}
