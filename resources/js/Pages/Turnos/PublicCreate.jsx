import { Head, Link, useForm, router } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { useState, useMemo, useEffect } from 'react';

export default function PublicCreate({ itemCatalogos, turnosDelDia, selectedDate, has_client, pending_turno, horario_apertura, horario_cierre, flash }) {
    const { data, setData, post, processing, errors } = useForm({
        item_catalogo_id: pending_turno?.item_catalogo_id || '',
        fecha_hora_inicio: pending_turno?.fecha_hora_inicio || '',
    });

    const [selectedDateUI, setSelectedDateUI] = useState(selectedDate);
    const [selectedSlot, setSelectedSlot] = useState(data.fecha_hora_inicio ? data.fecha_hora_inicio.split('T')[1]?.substring(0, 5) : '');

    // Refetch when date changes
    useEffect(() => {
        if (selectedDateUI && selectedDateUI !== selectedDate) {
            router.get(route('public.turno.create'), { date: selectedDateUI }, { preserveState: true, replace: true });
            // Reset slot
            setSelectedSlot('');
            setData('fecha_hora_inicio', '');
        }
    }, [selectedDateUI, selectedDate]);

    // Calcular slots
    const availableSlots = useMemo(() => {
        if (!data.item_catalogo_id || !selectedDateUI || !horario_apertura || !horario_cierre) return [];

        const selectedItem = itemCatalogos.find(i => i.id.toString() === data.item_catalogo_id.toString());
        if (!selectedItem) return [];

        const duration = selectedItem.duracion_minutos;

        // Parse boundaries
        const [openHour, openMin] = horario_apertura.split(':').map(Number);
        const [closeHour, closeMin] = horario_cierre.split(':').map(Number);

        let current = new Date(selectedDateUI.replace(/-/g, '/') + ' 00:00:00');
        current.setHours(openHour, openMin, 0);

        const endLimit = new Date(selectedDateUI.replace(/-/g, '/') + ' 00:00:00');
        endLimit.setHours(closeHour, closeMin, 0);

        const slots = [];
        
        while (true) {
            const slotEnd = new Date(current.getTime() + duration * 60000);
            if (slotEnd > endLimit) break;

            // Check overlap con la misma lógica que CheckTurnoOverlapAction (incluyendo buffer 5 min)
            let isOverlapping = false;
            const bufferMs = 5 * 60000;

            for (const t of turnosDelDia) {
                // Parseamos ignorando la zona horaria (los turnos vienen como Y-m-d H:i:s, así que JS los asume locales)
                // Usamos replace para asegurar que Safari/Firefox lo parseen bien
                const tStart = new Date(t.fecha_hora_inicio.replace(/-/g, '/'));
                const tEnd = new Date(t.fecha_hora_fin.replace(/-/g, '/'));

                const inicioConBuffer = new Date(current.getTime() - bufferMs);
                const finConBuffer = new Date(slotEnd.getTime() + bufferMs);

                // Overlap condition: A_fin > B_inicio - buffer Y A_inicio < B_fin + buffer
                if (tEnd > inicioConBuffer && tStart < finConBuffer) {
                    isOverlapping = true;
                    break;
                }
            }

            // Also check if slot is in the past (if today)
            const now = new Date();
            if (current < now) {
                isOverlapping = true;
            }

            if (!isOverlapping) {
                const hours = current.getHours().toString().padStart(2, '0');
                const mins = current.getMinutes().toString().padStart(2, '0');
                slots.push(`${hours}:${mins}`);
            }

            // Avanzamos 15 minutos (menor granularidad para encontrar huecos tras el buffer)
            current.setMinutes(current.getMinutes() + 15);
        }

        return slots;
    }, [data.item_catalogo_id, selectedDateUI, itemCatalogos, turnosDelDia, horario_apertura, horario_cierre]);

    const handleSlotClick = (slot) => {
        setSelectedSlot(slot);
        setData('fecha_hora_inicio', `${selectedDateUI}T${slot}`);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('public.turno.store'));
    };

    return (
        <>
            <Head title="Reservar Turno" />
            
            <div className="min-h-screen bg-gray-100 dark:bg-gray-900 py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold">Reservar un Turno</h2>
                                <Link href="/" className="text-sm text-indigo-600 dark:text-indigo-400 underline">Volver al inicio</Link>
                            </div>

                            {flash?.status && (
                                <div className="mb-4 font-medium text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 p-4 rounded">
                                    {flash.status}
                                </div>
                            )}

                            <form onSubmit={submit} className="space-y-8">
                                {/* Paso 1: Servicio */}
                                <div>
                                    <h3 className="text-lg font-medium mb-4">1. Elige tu servicio</h3>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        {itemCatalogos.map(item => (
                                            <div 
                                                key={item.id}
                                                onClick={() => {
                                                    setData('item_catalogo_id', item.id.toString());
                                                    setSelectedSlot('');
                                                    setData('fecha_hora_inicio', '');
                                                }}
                                                className={`border rounded-lg p-4 cursor-pointer transition ${data.item_catalogo_id === item.id.toString() ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300'}`}
                                            >
                                                <div className="font-bold">{item.nombre}</div>
                                                <div className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    Duración: {item.duracion_minutos} min
                                                </div>
                                                <div className="text-lg font-semibold text-indigo-600 dark:text-indigo-400 mt-2">
                                                    ${item.precio}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    <InputError className="mt-2" message={errors.item_catalogo_id} />
                                </div>

                                {/* Paso 2: Fecha */}
                                {data.item_catalogo_id && (
                                    <div>
                                        <h3 className="text-lg font-medium mb-4">2. Selecciona la fecha</h3>
                                        <input
                                            type="date"
                                            className="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full sm:w-1/2"
                                            value={selectedDateUI}
                                            min={new Date().toISOString().split('T')[0]}
                                            onChange={(e) => setSelectedDateUI(e.target.value)}
                                        />
                                    </div>
                                )}

                                {/* Paso 3: Hora */}
                                {data.item_catalogo_id && selectedDateUI && (
                                    <div>
                                        <h3 className="text-lg font-medium mb-4">3. Horarios disponibles</h3>
                                        
                                        {availableSlots.length > 0 ? (
                                            <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                                {availableSlots.map(slot => (
                                                    <div
                                                        key={slot}
                                                        onClick={() => handleSlotClick(slot)}
                                                        className={`text-center py-2 border rounded cursor-pointer transition ${selectedSlot === slot ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500'}`}
                                                    >
                                                        {slot}
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-gray-500 dark:text-gray-400 italic">No hay horarios disponibles para esta fecha.</p>
                                        )}
                                        <InputError className="mt-2" message={errors.fecha_hora_inicio} />
                                    </div>
                                )}

                                {/* Submit */}
                                {data.item_catalogo_id && data.fecha_hora_inicio && (
                                    <div className="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                        <PrimaryButton disabled={processing} className="px-6 py-3 text-lg">
                                            {has_client ? 'Confirmar Reserva' : 'Ingresar / Registrarse para Confirmar'}
                                        </PrimaryButton>
                                    </div>
                                )}
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
