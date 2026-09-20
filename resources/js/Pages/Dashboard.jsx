import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ cantidadTurnosHoy, ingresosEstimadosHoy, servicioMasSolicitado }) {
    // Formatear moneda en ARS
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 0,
        }).format(value);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Resumen del Día
                    </h2>
                    {/* Espacio reservado para el botón de agendado rápido (HU-TUR-01 refine) */}
                    <div id="quick-schedule-placeholder"></div>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    
                    {cantidadTurnosHoy === 0 ? (
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-12 text-center text-gray-500">
                                <svg className="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 className="text-lg font-medium text-gray-900">Sin turnos hoy</h3>
                                <p className="mt-1 text-sm text-gray-500">No hay turnos agendados para el día de la fecha.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {/* Tarjeta: Cantidad de turnos */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Turnos del día
                                    </dt>
                                    <dd className="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                                        {cantidadTurnosHoy}
                                    </dd>
                                </div>
                            </div>

                            {/* Tarjeta: Ingresos estimados */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Ingresos estimados
                                    </dt>
                                    <dd className="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                                        {formatCurrency(ingresosEstimadosHoy)}
                                    </dd>
                                </div>
                            </div>

                            {/* Tarjeta: Servicio más solicitado */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Servicio más solicitado
                                    </dt>
                                    <dd className="mt-2 truncate text-3xl font-semibold tracking-tight text-gray-900">
                                        {servicioMasSolicitado || '-'}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
