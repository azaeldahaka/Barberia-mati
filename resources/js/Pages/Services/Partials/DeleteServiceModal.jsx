import Modal from '@/Components/Modal';
import DangerButton from '@/Components/DangerButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { useForm } from '@inertiajs/react';

export default function DeleteServiceModal({ show, onClose, service }) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (!service) return;
        
        destroy(route('services.destroy', service.id), {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Modal show={show} onClose={onClose}>
            <div className="p-6">
                <h2 className="text-lg font-medium text-gray-900">
                    ¿Estás seguro de eliminar el servicio "{service?.name}"?
                </h2>

                <p className="mt-1 text-sm text-gray-600">
                    Este servicio dejará de estar disponible para futuros turnos, pero se mantendrá en el historial de los turnos ya agendados.
                </p>

                <div className="mt-6 flex justify-end">
                    <SecondaryButton onClick={onClose}>Cancelar</SecondaryButton>

                    <DangerButton className="ms-3" disabled={processing} onClick={handleDelete}>
                        Eliminar Servicio
                    </DangerButton>
                </div>
            </div>
        </Modal>
    );
}
