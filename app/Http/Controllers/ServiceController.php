<?php

namespace App\Http\Controllers;

use App\Actions\Services\CreateServiceAction;
use App\Actions\Services\DeleteServiceAction;
use App\Actions\Services\UpdateServiceAction;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $services = Service::with('itemCatalogo')->get()->sortBy('itemCatalogo.nombre')->values()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->itemCatalogo->nombre,
                'price' => $service->itemCatalogo->precio,
                'duration_minutes' => $service->itemCatalogo->duracion_minutos,
                'cuenta_para_fidelizacion' => $service->cuenta_para_fidelizacion,
            ];
        });

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function store(StoreServiceRequest $request, CreateServiceAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('services.index')->with('success', 'Servicio creado correctamente.');
    }

    public function update(UpdateServiceRequest $request, Service $service, UpdateServiceAction $action): RedirectResponse
    {
        $action->execute($service, $request->validated());

        return redirect()->route('services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service, DeleteServiceAction $action): RedirectResponse
    {
        $action->execute($service);

        return redirect()->route('services.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
