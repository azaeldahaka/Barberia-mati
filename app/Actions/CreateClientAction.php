<?php

namespace App\Actions;

use App\Models\Client;

class CreateClientAction
{
    /**
     * Handle the creation of a new client.
     */
    public function execute(array $data): Client
    {
        return Client::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
        ]);
    }
}
