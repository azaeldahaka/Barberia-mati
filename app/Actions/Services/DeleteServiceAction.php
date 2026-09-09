<?php

namespace App\Actions\Services;

use App\Models\Service;

class DeleteServiceAction
{
    public function execute(Service $service): void
    {
        // Delete the service first
        $service->delete();

        // Then delete the item catalogo associated
        if ($service->itemCatalogo) {
            $service->itemCatalogo->delete();
        }
    }
}
