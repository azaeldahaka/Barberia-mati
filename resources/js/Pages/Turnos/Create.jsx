import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useState } from 'react';

export default function Create({ clients, itemCatalogos }) {
    const [isNewClient, setIsNewClient] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        client_id: '',
        first_name: '',
        last_name: '',
        phone: '',
        item_catalogo_id: '',
        fecha_hora_inicio: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('turnos.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Agendar Turno (Staff)</h2>}
        >
            <Head title="Agendar Turno" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={submit} className="space-y-6 max-w-xl">
                            <div>
                                <InputLabel value="Cliente" />
                                <div className="mt-2 flex items-center gap-4">
                                    <label className="flex items-center">
                                        <input
                                            type="radio"
                                            checked={!isNewClient}
                                            onChange={() => {
                                                setIsNewClient(false);
                                                setData('first_name', '');
                                                setData('last_name', '');
                                                setData('phone', '');
                                            }}
                                            className="mr-2"
                                        />
                                        Cliente Existente
                                    </label>
                                    <label className="flex items-center">
                                        <input
                                            type="radio"
                                            checked={isNewClient}
                                            onChange={() => {
                                                setIsNewClient(true);
                                                setData('client_id', '');
                                            }}
                                            className="mr-2"
                                        />
                                        Cliente Nuevo
                                    </label>
                                </div>
                            </div>

                            {!isNewClient ? (
                                <div>
                                    <InputLabel htmlFor="client_id" value="Seleccionar Cliente" />
                                    <select
                                        id="client_id"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.client_id}
                                        onChange={(e) => setData('client_id', e.target.value)}
                                        required={!isNewClient}
                                    >
                                        <option value="">Seleccione...</option>
                                        {clients.map((client) => (
                                            <option key={client.id} value={client.id}>
                                                {client.first_name} {client.last_name} ({client.phone})
                                            </option>
                                        ))}
                                    </select>
                                    <InputError className="mt-2" message={errors.client_id} />
                                </div>
                            ) : (
                                <div className="space-y-4 border p-4 rounded-md bg-gray-50">
                                    <div>
                                        <InputLabel htmlFor="first_name" value="Nombre" />
                                        <TextInput
                                            id="first_name"
                                            type="text"
                                            className="mt-1 block w-full"
                                            value={data.first_name}
                                            onChange={(e) => setData('first_name', e.target.value)}
                                            required={isNewClient}
                                        />
                                        <InputError className="mt-2" message={errors.first_name} />
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="last_name" value="Apellido" />
                                        <TextInput
                                            id="last_name"
                                            type="text"
                                            className="mt-1 block w-full"
                                            value={data.last_name}
                                            onChange={(e) => setData('last_name', e.target.value)}
                                            required={isNewClient}
                                        />
                                        <InputError className="mt-2" message={errors.last_name} />
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="phone" value="Teléfono" />
                                        <TextInput
                                            id="phone"
                                            type="text"
                                            className="mt-1 block w-full"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            required={isNewClient}
                                        />
                                        <InputError className="mt-2" message={errors.phone} />
                                    </div>
                                </div>
                            )}

                            <div>
                                <InputLabel htmlFor="item_catalogo_id" value="Servicio / Combo" />
                                <select
                                    id="item_catalogo_id"
                                    className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    value={data.item_catalogo_id}
                                    onChange={(e) => setData('item_catalogo_id', e.target.value)}
                                    required
                                >
                                    <option value="">Seleccione...</option>
                                    {itemCatalogos.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.nombre} - ${item.precio} ({item.duracion_minutos} min)
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.item_catalogo_id} />
                            </div>

                            <div>
                                <InputLabel htmlFor="fecha_hora_inicio" value="Fecha y Hora de Inicio" />
                                <TextInput
                                    id="fecha_hora_inicio"
                                    type="datetime-local"
                                    className="mt-1 block w-full"
                                    value={data.fecha_hora_inicio}
                                    onChange={(e) => setData('fecha_hora_inicio', e.target.value)}
                                    required
                                />
                                <InputError className="mt-2" message={errors.fecha_hora_inicio} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Agendar Turno
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
