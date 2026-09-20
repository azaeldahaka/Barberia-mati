import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import BackButton from '@/Components/BackButton';
import TurnoForm from '@/Components/TurnoForm';

export default function Create({ clients, itemCatalogos }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center">
                    <BackButton fallback={route('turnos.index')} className="mr-4" />
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Agendar Turno (Staff)</h2>
                </div>
            }
        >
            <Head title="Agendar Turno" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="max-w-xl">
                            <TurnoForm clients={clients} itemCatalogos={itemCatalogos} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
