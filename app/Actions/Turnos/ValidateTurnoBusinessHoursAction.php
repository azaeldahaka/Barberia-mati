<?php

namespace App\Actions\Turnos;

use App\Models\Barberia;
use Carbon\Carbon;

class ValidateTurnoBusinessHoursAction
{
    /**
     * Valida si un turno propuesto se encuentra dentro del horario de atención de la barbería.
     */
    public function execute(Barberia $barberia, Carbon $fechaHoraInicio, Carbon $fechaHoraFin): bool
    {
        // Se asume que fechaHoraInicio y fechaHoraFin ocurren el mismo día.
        $baseDateInicio = $fechaHoraInicio->copy()->startOfDay();
        $baseDateFin = $fechaHoraFin->copy()->startOfDay();

        // Extraer horas y minutos de los strings 'HH:MM' o 'HH:MM:SS'
        $aperturaParts = explode(':', $barberia->horario_apertura);
        $cierreParts = explode(':', $barberia->horario_cierre);

        $apertura = $baseDateInicio->copy()->setTime((int) $aperturaParts[0], (int) $aperturaParts[1]);
        $cierre = $baseDateInicio->copy()->setTime((int) $cierreParts[0], (int) $cierreParts[1]);

        // Si el turno cruza la medianoche, lo consideramos fuera de horario
        if ($baseDateInicio->notEqualTo($baseDateFin)) {
            return false;
        }

        // El inicio debe ser mayor o igual a la apertura
        // El fin debe ser menor o igual al cierre
        return $fechaHoraInicio->greaterThanOrEqualTo($apertura) && $fechaHoraFin->lessThanOrEqualTo($cierre);
    }
}
