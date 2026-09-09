import { useEffect } from 'react';
import Modal from '@/Components/Modal';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function ServiceFormModal({ show, onClose, service }) {
    const isEdit = !!service;

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        name: '',
        duration_minutes: '',
        price: '',
    });

    useEffect(() => {
        if (show) {
            if (isEdit) {
                setData({
                    name: service.name,
                    duration_minutes: service.duration_minutes,
                    price: service.price,
                });
            } else {
                reset();
            }
            clearErrors();
        }
    }, [show, service]);

    const handleSubmit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('services.update', service.id), {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        } else {
            post(route('services.store'), {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        }
    };

    return (
        <Modal show={show} onClose={onClose}>
            <form onSubmit={handleSubmit} className="p-6">
                <h2 className="text-lg font-medium text-gray-900">
                    {isEdit ? 'Editar Servicio' : 'Nuevo Servicio'}
                </h2>

                <div className="mt-6">
                    <InputLabel htmlFor="name" value="Nombre del Servicio" />

                    <TextInput
                        id="name"
                        type="text"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-6">
                    <InputLabel htmlFor="duration_minutes" value="Duración (minutos)" />

                    <TextInput
                        id="duration_minutes"
                        type="number"
                        min="1"
                        className="mt-1 block w-full"
                        value={data.duration_minutes}
                        onChange={(e) => setData('duration_minutes', e.target.value)}
                        required
                    />

                    <InputError message={errors.duration_minutes} className="mt-2" />
                </div>

                <div className="mt-6">
                    <InputLabel htmlFor="price" value="Precio ($)" />

                    <TextInput
                        id="price"
                        type="number"
                        min="0"
                        step="0.01"
                        className="mt-1 block w-full"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        required
                    />

                    <InputError message={errors.price} className="mt-2" />
                </div>

                <div className="mt-6 flex justify-end">
                    <SecondaryButton type="button" onClick={onClose}>Cancelar</SecondaryButton>

                    <PrimaryButton className="ms-3" disabled={processing}>
                        {isEdit ? 'Guardar Cambios' : 'Crear Servicio'}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}
