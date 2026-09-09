<?php

namespace App\Actions\Turnos;

use App\Models\ItemCatalogo;
use Carbon\Carbon;

class CalculateTurnoEndTimeAction
{
    /**
     * Calcula la fecha y hora de finalización de un turno basándose en la
     * duración del ítem (servicio o combo) seleccionado.
     */
    public function execute(ItemCatalogo $itemCatalogo, Carbon $fechaHoraInicio): Carbon
    {
        return $fechaHoraInicio->copy()->addMinutes($itemCatalogo->duracion_minutos);
    }
}
