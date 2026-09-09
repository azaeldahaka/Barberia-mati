import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import ServiceFormModal from './Partials/ServiceFormModal';
import DeleteServiceModal from './Partials/DeleteServiceModal';

export default function Index({ auth, services }) {
    const { flash } = usePage().props;
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [selectedService, setSelectedService] = useState(null);

    const openCreateModal = () => {
        setSelectedService(null);
        setIsFormModalOpen(true);
    };

    const openEditModal = (service) => {
        setSelectedService(service);
        setIsFormModalOpen(true);
    };

    const openDeleteModal = (service) => {
        setSelectedService(service);
        setIsDeleteModalOpen(true);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Servicios</h2>}
        >
            <Head title="Servicios" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="mb-4 font-medium text-sm text-green-600 bg-green-100 p-4 rounded-md">
                            {flash.success}
                        </div>
                    )}
                    
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-medium text-gray-900">Catálogo de Servicios</h3>
                                <PrimaryButton onClick={openCreateModal}>
                                    Nuevo Servicio
                                </PrimaryButton>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración (min)</th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                            <th scope="col" className="relative px-6 py-3"><span className="sr-only">Acciones</span></th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {services.map((service) => (
                                            <tr key={service.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{service.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{service.duration_minutes}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${service.price}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onClick={() => openEditModal(service)} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                                    <button onClick={() => openDeleteModal(service)} className="text-red-600 hover:text-red-900">Eliminar</button>
                                                </td>
                                            </tr>
                                        ))}
                                        {services.length === 0 && (
                                            <tr>
                                                <td colSpan="4" className="px-6 py-4 text-center text-sm text-gray-500">
                                                    No hay servicios registrados.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ServiceFormModal 
                show={isFormModalOpen} 
                onClose={() => setIsFormModalOpen(false)} 
                service={selectedService} 
            />

            <DeleteServiceModal 
                show={isDeleteModalOpen} 
                onClose={() => setIsDeleteModalOpen(false)} 
                service={selectedService} 
            />
        </AuthenticatedLayout>
    );
}
