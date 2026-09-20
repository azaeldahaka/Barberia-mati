import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import BackButton from '@/Components/BackButton';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

// Helpers para fechas
const getStartOfWeek = (date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Lunes
    return new Date(d.setDate(diff));
};

const addDays = (date, days) => {
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    return d;
};

const formatDateForInput = (date) => {
    const d = new Date(date);
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${m}-${day}`;
};

const HOURS = [12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22]; // Horario fijo según HU-TUR-08
const PIXELS_PER_MINUTE = 1; // 60px por hora
const HOUR_HEIGHT = 60;
const START_HOUR = 12;
const MIN_BLOCK_HEIGHT = 45; // Minimum height for readability

export default function Index({ turnos, itemCatalogos, filters }) {
    const [viewType, setViewType] = useState(filters.view || 'day');
    const [selectedTurno, setSelectedTurno] = useState(null);
    const [isEditing, setIsEditing] = useState(false);
    
    // Convertimos de '2026-09-10' a Date local en tiempo de montado
    const [currentDate, setCurrentDate] = useState(() => {
        return filters.start ? new Date(filters.start + 'T00:00:00') : new Date();
    });

    const { data, setData, put, processing, errors, reset, clearErrors } = useForm({
        item_catalogo_id: '',
        fecha_hora_inicio: '',
        estado: '',
    });

    const openTurnoModal = (turno) => {
        setSelectedTurno(turno);
        setIsEditing(false);
        setData({
            item_catalogo_id: turno.item_catalogo_id,
            fecha_hora_inicio: turno.fecha_hora_inicio.replace(' ', 'T'),
            estado: turno.estado,
        });
        clearErrors();
    };

    const closeTurnoModal = () => {
        setSelectedTurno(null);
        setIsEditing(false);
        reset();
        clearErrors();
    };

    const handleEditSubmit = (e) => {
        e.preventDefault();
        put(route('turnos.update', selectedTurno.id), {
            onSuccess: () => {
                closeTurnoModal();
            },
        });
    };

    const changeDateRange = (newDate, newViewType) => {
        let start, end;
        if (newViewType === 'day') {
            start = formatDateForInput(newDate);
            end = formatDateForInput(newDate);
        } else {
            const startOfWeek = getStartOfWeek(newDate);
            start = formatDateForInput(startOfWeek);
            end = formatDateForInput(addDays(startOfWeek, 6)); // Domingo
        }

        router.get(route('turnos.index'), { start, end, view: newViewType }, { preserveState: true, replace: true });
    };

    const handlePrev = () => {
        const newDate = addDays(currentDate, viewType === 'day' ? -1 : -7);
        setCurrentDate(newDate);
        changeDateRange(newDate, viewType);
    };

    const handleNext = () => {
        const newDate = addDays(currentDate, viewType === 'day' ? 1 : 7);
        setCurrentDate(newDate);
        changeDateRange(newDate, viewType);
    };

    const handleToday = () => {
        const newDate = new Date();
        setCurrentDate(newDate);
        changeDateRange(newDate, viewType);
    };

    const handleViewChange = (newView) => {
        setViewType(newView);
        changeDateRange(currentDate, newView);
    };

    // Función para calcular estilos absolutos del bloque del turno, con altura mínima sin superposición
    const getTurnoStyle = (turno, dayTurnos) => {
        const safeDateStr = turno.fecha_hora_inicio.replace(' ', 'T');
        const start = new Date(safeDateStr);
        
        const safeEndDateStr = turno.fecha_hora_fin.replace(' ', 'T');
        const end = new Date(safeEndDateStr);

        const hours = start.getHours();
        const minutes = start.getMinutes();

        const durationMinutes = (end - start) / 60000;
        const actualHeight = durationMinutes * PIXELS_PER_MINUTE;
        const top = ((hours - START_HOUR) * 60 + minutes) * PIXELS_PER_MINUTE;

        // Find the next shift in the same day to calculate available gap
        const nextTurnos = dayTurnos.filter(t => {
            const tStart = new Date(t.fecha_hora_inicio.replace(' ', 'T'));
            return tStart > start;
        }).sort((a, b) => new Date(a.fecha_hora_inicio.replace(' ', 'T')) - new Date(b.fecha_hora_inicio.replace(' ', 'T')));

        let maxAllowedHeight = Infinity;
        if (nextTurnos.length > 0) {
            const nextStart = new Date(nextTurnos[0].fecha_hora_inicio.replace(' ', 'T'));
            const gapMinutes = (nextStart - start) / 60000;
            maxAllowedHeight = gapMinutes * PIXELS_PER_MINUTE;
        }

        // The height should be at least MIN_BLOCK_HEIGHT, but not exceeding maxAllowedHeight
        // If maxAllowedHeight < actualHeight (shouldn't happen due to overlap validation, but just in case), use actualHeight
        const height = Math.max(actualHeight, Math.min(MIN_BLOCK_HEIGHT, maxAllowedHeight));

        return {
            top: `${top}px`,
            height: `${height}px`,
            position: 'absolute',
            left: '4px',
            right: '4px',
        };
    };

    // Filtramos turnos por día (útil para vista semanal)
    const getTurnosForDate = (date) => {
        const dateStr = formatDateForInput(date);
        return turnos.filter(t => t.fecha_hora_inicio.startsWith(dateStr));
    };

    // Render de un día específico
    const renderDayColumn = (date, isWeekView = false) => {
        const dayTurnos = getTurnosForDate(date);
        
        return (
            <div key={date.toISOString()} className={`relative flex-1 border-r border-gray-200 min-w-[120px]`}>
                {isWeekView && (
                    <div className="text-center py-2 border-b border-gray-200 bg-gray-50 font-medium text-sm">
                        {date.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short' })}
                    </div>
                )}
                
                <div className="relative" style={{ height: `${HOURS.length * HOUR_HEIGHT}px` }}>
                    {/* Grilla de fondo (líneas de hora) */}
                    {HOURS.map((h) => (
                        <div key={h} className="absolute w-full border-t border-gray-100" style={{ top: `${(h - START_HOUR) * HOUR_HEIGHT}px`, height: `${HOUR_HEIGHT}px` }}>
                        </div>
                    ))}
                    
                    {/* Turnos */}
                    {dayTurnos.map(turno => {
                        const isAusente = turno.estado === 'ausente';
                        const style = getTurnoStyle(turno, dayTurnos);
                        const durationMinutes = (new Date(turno.fecha_hora_fin.replace(' ', 'T')) - new Date(turno.fecha_hora_inicio.replace(' ', 'T'))) / 60000;
                        const isShort = durationMinutes < 30; // Consider short if less than 30 mins

                        return (
                            <div
                                key={turno.id}
                                onClick={() => openTurnoModal(turno)}
                                className={`cursor-pointer rounded-md p-1 shadow-sm border text-xs z-10 transition-colors flex flex-col justify-start overflow-hidden
                                    ${isAusente ? 'bg-gray-100 border-gray-300 text-gray-500 opacity-80' : 'bg-blue-100 border-blue-300 text-blue-800'}`}
                                style={style}
                                title={`${turno.item_catalogo.nombre} - ${turno.cliente.first_name} ${turno.cliente.last_name}`}
                            >
                                <div className="font-semibold truncate leading-tight flex justify-between items-center gap-1">
                                    <span className="truncate">{turno.cliente.first_name} {turno.cliente.last_name}</span>
                                    <span className="font-normal whitespace-nowrap hidden sm:inline">${turno.item_catalogo.precio}</span>
                                </div>
                                <div className="truncate leading-tight opacity-90">
                                    {turno.item_catalogo.nombre}
                                </div>
                                <div className="text-[10px] opacity-75 truncate">
                                    {turno.fecha_hora_inicio.substring(11, 16)} - {turno.fecha_hora_fin.substring(11, 16)}
                                </div>
                            </div>
                        )
                    })}
                </div>
            </div>
        );
    };

    // Días de la semana para vista semanal
    const weekDays = [];
    if (viewType === 'week') {
        const startOfWeek = getStartOfWeek(currentDate);
        for (let i = 0; i < 7; i++) {
            weekDays.push(addDays(startOfWeek, i));
        }
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <div className="flex items-center">
                        <BackButton fallback={route('dashboard')} className="mr-4" />
                        <h2 className="font-semibold text-xl text-gray-800 leading-tight">Agenda (Turnos)</h2>
                    </div>
                    <Link
                        href={route('turnos.create')}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                    >
                        + Nuevo Turno
                    </Link>
                </div>
            }
        >
            <Head title="Turnos" />

            <div className="py-6 flex-1 overflow-hidden flex flex-col">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 w-full flex-1">
                    <div className="bg-white shadow-sm sm:rounded-lg">
                        
                        {/* Toolbar */}
                        <div className="flex flex-col sm:flex-row justify-between items-center p-4 border-b border-gray-200 gap-4">
                            <div className="flex items-center space-x-2">
                                <button onClick={handleToday} className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 font-medium">Hoy</button>
                                <button onClick={handlePrev} className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">&lt;</button>
                                <button onClick={handleNext} className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">&gt;</button>
                                <span className="font-medium ml-2 text-gray-700">
                                    {viewType === 'day' 
                                        ? currentDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
                                        : `Semana del ${getStartOfWeek(currentDate).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })}`}
                                </span>
                            </div>

                            <div className="flex bg-gray-100 rounded-lg p-1">
                                <button 
                                    onClick={() => handleViewChange('day')}
                                    className={`px-4 py-1 text-sm rounded-md transition-colors ${viewType === 'day' ? 'bg-white shadow-sm font-medium' : 'text-gray-500'}`}
                                >
                                    Día
                                </button>
                                <button 
                                    onClick={() => handleViewChange('week')}
                                    className={`px-4 py-1 text-sm rounded-md transition-colors ${viewType === 'week' ? 'bg-white shadow-sm font-medium' : 'text-gray-500'}`}
                                >
                                    Semana
                                </button>
                            </div>
                        </div>

                        {/* Calendar Body */}
                        <div className="flex bg-white overflow-x-auto">
                            {/* Columna de horas */}
                            <div className="w-16 flex-shrink-0 border-r border-gray-200 bg-gray-50">
                                {viewType === 'week' && <div className="h-[37px] border-b border-gray-200"></div>}
                                <div className="relative" style={{ height: `${HOURS.length * HOUR_HEIGHT}px` }}>
                                    {HOURS.map((h) => (
                                        <div key={h} className="absolute w-full text-right pr-2 text-xs text-gray-500 -mt-2 font-medium" style={{ top: `${(h - START_HOUR) * HOUR_HEIGHT}px` }}>
                                            {h}:00
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Columnas de Días */}
                            {viewType === 'day' ? (
                                renderDayColumn(currentDate, false)
                            ) : (
                                weekDays.map((d) => renderDayColumn(d, true))
                            )}
                        </div>
                        
                    </div>
                </div>
            </div>

            {/* Turno Detail/Edit Modal */}
            <Modal show={!!selectedTurno} onClose={closeTurnoModal}>
                {selectedTurno && (
                    <div className="p-6">
                        <div className="flex justify-between items-start mb-4">
                            <h2 className="text-xl font-bold text-gray-900">
                                {isEditing ? 'Editar Turno' : 'Detalles del Turno'}
                            </h2>
                            <button onClick={closeTurnoModal} className="text-gray-400 hover:text-gray-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {!isEditing ? (
                            <div className="space-y-4">
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <span className="block text-sm text-gray-500">Cliente</span>
                                            <span className="font-semibold">{selectedTurno.cliente.first_name} {selectedTurno.cliente.last_name}</span>
                                            {selectedTurno.cliente.apodo && (
                                                <span className="text-gray-500 text-sm ml-1">"{selectedTurno.cliente.apodo}"</span>
                                            )}
                                        </div>
                                        <div>
                                            <span className="block text-sm text-gray-500">Servicio</span>
                                            <span className="font-semibold">{selectedTurno.item_catalogo.nombre}</span>
                                        </div>
                                        <div>
                                            <span className="block text-sm text-gray-500">Fecha</span>
                                            <span className="font-semibold capitalize">
                                                {new Date(selectedTurno.fecha_hora_inicio.replace(' ', 'T')).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="block text-sm text-gray-500">Horario</span>
                                            <span className="font-semibold">
                                                {selectedTurno.fecha_hora_inicio.substring(11, 16)} - {selectedTurno.fecha_hora_fin.substring(11, 16)}
                                            </span>
                                            <span className="text-gray-500 text-sm ml-2">
                                                ({selectedTurno.item_catalogo.duracion_minutos} min)
                                            </span>
                                        </div>
                                        <div>
                                            <span className="block text-sm text-gray-500">Costo</span>
                                            <span className="font-semibold text-green-600">${selectedTurno.item_catalogo.precio}</span>
                                        </div>
                                        <div>
                                            <span className="block text-sm text-gray-500">Estado</span>
                                            <span className={`inline-block px-2 py-1 rounded text-xs font-semibold uppercase
                                                ${selectedTurno.estado === 'reservado' ? 'bg-blue-100 text-blue-800' : 
                                                  selectedTurno.estado === 'ausente' ? 'bg-gray-200 text-gray-600' : 
                                                  'bg-green-100 text-green-800'}`}>
                                                {selectedTurno.estado}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex justify-end pt-4">
                                    <PrimaryButton onClick={() => setIsEditing(true)}>
                                        Editar
                                    </PrimaryButton>
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={handleEditSubmit} className="space-y-4">
                                <div>
                                    <InputLabel htmlFor="item_catalogo_id" value="Servicio" />
                                    <select
                                        id="item_catalogo_id"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.item_catalogo_id}
                                        onChange={e => setData('item_catalogo_id', e.target.value)}
                                        required
                                    >
                                        <option value="">Seleccione un servicio</option>
                                        {itemCatalogos?.map(item => (
                                            <option key={item.id} value={item.id}>
                                                {item.nombre} - ${item.precio} ({item.duracion_minutos} min)
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.item_catalogo_id} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="fecha_hora_inicio" value="Fecha y Hora de Inicio" />
                                    <input
                                        type="datetime-local"
                                        id="fecha_hora_inicio"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.fecha_hora_inicio}
                                        onChange={e => setData('fecha_hora_inicio', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.fecha_hora_inicio} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="estado" value="Estado" />
                                    <select
                                        id="estado"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.estado}
                                        onChange={e => setData('estado', e.target.value)}
                                        required
                                    >
                                        <option value="reservado">Reservado</option>
                                        <option value="confirmado">Confirmado</option>
                                        <option value="completado">Completado</option>
                                        <option value="ausente">Ausente</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                    <InputError message={errors.estado} className="mt-2" />
                                </div>

                                <div className="flex justify-end gap-2 pt-4">
                                    <SecondaryButton onClick={() => setIsEditing(false)}>
                                        Cancelar
                                    </SecondaryButton>
                                    <PrimaryButton disabled={processing}>
                                        Guardar Cambios
                                    </PrimaryButton>
                                </div>
                            </form>
                        )}
                    </div>
                )}
            </Modal>
        </AuthenticatedLayout>
    );
}
