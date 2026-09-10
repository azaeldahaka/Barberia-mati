<?php

namespace App\Actions\Turnos;

use App\Models\Turno;
use Carbon\Carbon;

class CheckTurnoOverlapAction
{
    /**
     * Verifica si existe algún turno que se superponga con el rango propuesto.
     * Retorna true si hay superposición, false si el horario está libre.
     */
    public function execute(string $barberiaId, Carbon $fechaHoraInicio, Carbon $fechaHoraFin): bool
    {
        return Turno::where('barberia_id', $barberiaId)
            ->whereIn('estado', ['reservado', 'completado'])
            ->where(function ($query) use ($fechaHoraInicio, $fechaHoraFin) {
                // Hay superposición si:
                // El turno existente empieza antes del fin propuesto
                // Y termina después del inicio propuesto
                $query->where('fecha_hora_inicio', '<', $fechaHoraFin)
                    ->where('fecha_hora_fin', '>', $fechaHoraInicio);
            })
            ->exists();
    }
}
