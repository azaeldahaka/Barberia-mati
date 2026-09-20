import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import BackButton from '@/Components/BackButton';
import { useState } from 'react';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/react';

export default function Create({ clients, itemCatalogos }) {
    const [isNewClient, setIsNewClient] = useState(false);
    const [query, setQuery] = useState('');

    const { data, setData, post, processing, errors, isDirty } = useForm({
        client_id: '',
        first_name: '',
        last_name: '',
        apodo: '',
        phone: '',
        item_catalogo_id: '',
        fecha_hora_inicio: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('turnos.store'));
    };

    const normalizeString = (str) =>
        str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase() : '';

    const filteredClients =
        query === ''
            ? clients
            : clients.filter((client) => {
                const normalizedQuery = normalizeString(query);
                return (
                    normalizeString(client.first_name).includes(normalizedQuery) ||
                    normalizeString(client.last_name).includes(normalizedQuery) ||
                    normalizeString(client.phone).includes(normalizedQuery) ||
                    normalizeString(client.apodo).includes(normalizedQuery)
                );
            });

    const selectedClient = clients.find(c => c.id === data.client_id) || null;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center">
                    <BackButton fallback={route('turnos.index')} isDirty={isDirty} className="mr-4" />
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Agendar Turno (Staff)</h2>
                </div>
            }
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
                                                setData('apodo', '');
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
                                                setQuery('');
                                            }}
                                            className="mr-2"
                                        />
                                        Cliente Nuevo
                                    </label>
                                </div>
                            </div>

                            {!isNewClient ? (
                                <div className="relative">
                                    <InputLabel htmlFor="client_id" value="Buscar Cliente" />
                                    <Combobox 
                                        value={selectedClient} 
                                        onChange={(client) => setData('client_id', client ? client.id : '')}
                                    >
                                        <div className="relative mt-1">
                                            <div className="relative w-full cursor-default overflow-hidden rounded-md bg-white text-left shadow-sm focus:outline-none sm:text-sm border border-gray-300">
                                                <ComboboxInput
                                                    className="w-full border-none py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 focus:ring-indigo-500"
                                                    displayValue={(client) => 
                                                        client 
                                                        ? `${client.first_name} ${client.apodo ? `"${client.apodo}" ` : ''}${client.last_name} (${client.phone})` 
                                                        : ''
                                                    }
                                                    onChange={(event) => setQuery(event.target.value)}
                                                    placeholder="Buscar por nombre, apellido, apodo o teléfono..."
                                                />
                                            </div>
                                            <ComboboxOptions className="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-10">
                                                {filteredClients.length === 0 && query !== '' ? (
                                                    <div className="relative cursor-default select-none px-4 py-2 text-gray-700">
                                                        No se encontraron clientes.
                                                    </div>
                                                ) : (
                                                    filteredClients.map((client) => (
                                                        <ComboboxOption
                                                            key={client.id}
                                                            className={({ focus }) =>
                                                                `relative cursor-default select-none py-2 pl-3 pr-4 ${
                                                                    focus ? 'bg-indigo-600 text-white' : 'text-gray-900'
                                                                }`
                                                            }
                                                            value={client}
                                                        >
                                                            {({ selected, focus }) => (
                                                                <>
                                                                    <span className={`block truncate ${selected ? 'font-medium' : 'font-normal'}`}>
                                                                        {client.first_name} {client.apodo ? <span className={focus ? 'text-indigo-200' : 'text-gray-500'}>"{client.apodo}"</span> : ''} {client.last_name} ({client.phone})
                                                                    </span>
                                                                </>
                                                            )}
                                                        </ComboboxOption>
                                                    ))
                                                )}
                                            </ComboboxOptions>
                                        </div>
                                    </Combobox>
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
                                        <InputLabel htmlFor="apodo" value="Apodo (Opcional)" />
                                        <TextInput
                                            id="apodo"
                                            type="text"
                                            className="mt-1 block w-full"
                                            value={data.apodo}
                                            onChange={(e) => setData('apodo', e.target.value)}
                                        />
                                        <InputError className="mt-2" message={errors.apodo} />
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
