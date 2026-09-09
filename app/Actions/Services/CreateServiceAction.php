<?php

namespace App\Actions\Services;

use App\Models\Service;
use App\Models\ItemCatalogo;

class CreateServiceAction
{
    public function execute(array $data): Service
    {
        $item = ItemCatalogo::create([
            'barberia_id' => auth()->user()->barberia_id,
            'tipo' => 'servicio',
            'nombre' => $data['name'],
            'precio' => $data['price'],
            'duracion_minutos' => $data['duration_minutes'],
        ]);

        return Service::create([
            'item_catalogo_id' => $item->id,
            'cuenta_para_fidelizacion' => $data['cuenta_para_fidelizacion'] ?? false,
        ]);
    }
}
