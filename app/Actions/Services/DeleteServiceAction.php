<?php

namespace App\Actions\Services;

use App\Models\Service;

class DeleteServiceAction
{
    public function execute(Service $service): void
    {
        $service->delete();
    }
}
