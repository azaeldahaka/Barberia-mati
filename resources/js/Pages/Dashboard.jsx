import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import TurnoForm from '@/Components/TurnoForm';
import { useState } from 'react';

export default function Dashboard({ turnosTotales, turnosCompletados, turnosPendientes, ingresosHoy, pendienteCobro, servicioMasSolicitado, clients, itemCatalogos }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isLoadingProps, setIsLoadingProps] = useState(false);

    // Formatear moneda en ARS
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 0,
        }).format(value);
    };

    const openModal = () => {
        if (!clients || !itemCatalogos) {
            setIsLoadingProps(true);
            router.reload({
                only: ['clients', 'itemCatalogos'],
                onSuccess: () => {
                    setIsLoadingProps(false);
                    setIsModalOpen(true);
                }
            });
        } else {
            setIsModalOpen(true);
        }
    };

    const closeModal = () => {
        setIsModalOpen(false);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Resumen del Día
                    </h2>
                    <div id="quick-schedule-placeholder">
                        <PrimaryButton onClick={openModal} disabled={isLoadingProps}>
                            {isLoadingProps ? 'Cargando...' : 'Agendar Turno Rápido'}
                        </PrimaryButton>
                    </div>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    
                    {turnosTotales === 0 ? (
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
                            {/* Tarjeta: Ingresos del día */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg relative border-l-4 border-green-500">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Ingresos del día
                                    </dt>
                                    <dd className="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                                        {formatCurrency(ingresosHoy)}
                                    </dd>
                                    <p className="mt-1 text-xs text-gray-500">Solo turnos completados</p>
                                </div>
                            </div>

                            {/* Tarjeta: Pendiente por cobrar */}
                            <div className="overflow-hidden bg-gray-50 shadow-sm sm:rounded-lg border border-gray-200 border-l-4 border-yellow-400">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Pendiente por cobrar
                                    </dt>
                                    <dd className="mt-2 text-3xl font-semibold tracking-tight text-gray-700">
                                        {formatCurrency(pendienteCobro)}
                                    </dd>
                                    <p className="mt-1 text-xs text-gray-500">Estimado para {turnosPendientes} {turnosPendientes === 1 ? 'turno' : 'turnos'} pendientes</p>
                                </div>
                            </div>

                            {/* Tarjeta: Cantidad de turnos */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <dt className="truncate text-sm font-medium text-gray-500">
                                        Cantidad de turnos
                                    </dt>
                                    <dd className="mt-2 text-3xl font-semibold tracking-tight text-gray-900 flex items-baseline gap-2">
                                        {turnosTotales}
                                    </dd>
                                    <p className="mt-1 text-xs text-gray-500">
                                        {turnosCompletados} atendidos / {turnosPendientes} pendientes
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <Modal show={isModalOpen} onClose={closeModal} maxWidth="xl">
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Agendar Turno Rápido</h2>
                    {clients && itemCatalogos ? (
                        <TurnoForm
                            clients={clients}
                            itemCatalogos={itemCatalogos}
                            isDashboard={true}
                            onSuccess={closeModal}
                        />
                    ) : (
                        <div className="flex justify-center p-4">
                            <span className="text-gray-500">Cargando...</span>
                        </div>
                    )}
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
