<?php

namespace App\Actions\Turnos;

use App\Models\Turno;
use Carbon\Carbon;

class CheckTurnoOverlapAction
{
    public const TURNO_BUFFER_MINUTOS = 5;

    /**
     * Verifica si existe algún turno que se superponga con el rango propuesto.
     * Retorna true si hay superposición, false si el horario está libre.
     */
    public function execute(string $barberiaId, Carbon $fechaHoraInicio, Carbon $fechaHoraFin): bool
    {
        return Turno::where('barberia_id', $barberiaId)
            ->whereIn('estado', ['reservado', 'completado'])
            ->where(function ($query) use ($fechaHoraInicio, $fechaHoraFin) {
                // Buffer para dejar entre turnos
                $buffer = self::TURNO_BUFFER_MINUTOS;

                $inicioConBuffer = $fechaHoraInicio->copy()->subMinutes($buffer);
                $finConBuffer = $fechaHoraFin->copy()->addMinutes($buffer);

                // Hay superposición si el turno existente (A) y el nuevo (B) no tienen al menos el buffer de separación:
                // A_fin + buffer > B_inicio Y B_fin + buffer > A_inicio
                // Lo que equivale a: A_fin > B_inicio - buffer Y A_inicio < B_fin + buffer
                $query->where('fecha_hora_fin', '>', $inicioConBuffer)
                    ->where('fecha_hora_inicio', '<', $finConBuffer);
            })
            ->exists();
    }
}
