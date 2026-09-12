import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

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

export default function Index({ turnos, filters }) {
    const [viewType, setViewType] = useState(filters.view || 'day');
    
    // Convertimos de '2026-09-10' a Date local en tiempo de montado
    const [currentDate, setCurrentDate] = useState(() => {
        return filters.start ? new Date(filters.start + 'T00:00:00') : new Date();
    });

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

    // Función para calcular estilos absolutos del bloque del turno
    const getTurnoStyle = (turno) => {
        // En Safari/iOS new Date("2026-09-10 14:00:00") falla.
        // Al usar serializeDate, viene "2026-09-10 14:00:00", lo reemplazamos por "T"
        const safeDateStr = turno.fecha_hora_inicio.replace(' ', 'T');
        const start = new Date(safeDateStr);
        
        const safeEndDateStr = turno.fecha_hora_fin.replace(' ', 'T');
        const end = new Date(safeEndDateStr);

        const hours = start.getHours();
        const minutes = start.getMinutes();

        const durationMinutes = (end - start) / 60000;

        const top = ((hours - START_HOUR) * 60 + minutes) * PIXELS_PER_MINUTE;
        const height = durationMinutes * PIXELS_PER_MINUTE;

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
        return turnos.filter(t => {
            return t.fecha_hora_inicio.startsWith(dateStr);
        });
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
                        return (
                            <div
                                key={turno.id}
                                className={`rounded-md p-1 overflow-hidden shadow-sm border text-xs z-10 transition-colors
                                    ${isAusente ? 'bg-gray-100 border-gray-300 text-gray-500 opacity-80' : 'bg-blue-100 border-blue-300 text-blue-800'}`}
                                style={getTurnoStyle(turno)}
                                title={`${turno.item_catalogo.nombre} - ${turno.cliente.first_name} ${turno.cliente.last_name}`}
                            >
                                <div className="font-semibold truncate">{turno.cliente.first_name} {turno.cliente.last_name}</div>
                                <div className="truncate">{turno.item_catalogo.nombre}</div>
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
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Agenda (Turnos)</h2>
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
        </AuthenticatedLayout>
    );
}
