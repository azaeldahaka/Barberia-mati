<?php

namespace App\Http\Controllers;

use App\Actions\CreateClientAction;
use App\Http\Requests\StoreClientRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientRegistrationController extends Controller
{
    /**
     * Show the client registration form.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(StoreClientRequest $request, CreateClientAction $action): RedirectResponse
    {
        $client = $action->execute($request->validated());

        // Save the client ID in the session to identify them for future reservations (HU-TUR-02)
        session(['client_id' => $client->id]);

        return redirect()->to('/')->with('status', 'Registro exitoso. Ya puedes reservar un turno.');
    }
}
