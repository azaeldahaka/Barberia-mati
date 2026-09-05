import React from 'react';
import { Head } from '@inertiajs/react';

export default function Test() {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <Head title="Test Inertia" />
            <div className="bg-white p-8 rounded-lg shadow-md">
                <h1 className="text-2xl font-bold text-gray-800">Inertia + React funciona!</h1>
                <p className="mt-4 text-gray-600">Este es un componente de prueba.</p>
            </div>
        </div>
    );
}
