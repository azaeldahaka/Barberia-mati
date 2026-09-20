<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateStaffTurnoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Staff only (auth middleware applied in routes)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_catalogo_id' => ['required', 'exists:item_catalogos,id'],
            'fecha_hora_inicio' => ['required', 'date'],
            'estado' => ['required', 'string', 'in:reservado,confirmado,cancelado,ausente,completado'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->fecha_hora_inicio)) {
                return;
            }

            // Permitir cancelar en cualquier momento
            if ($this->estado === 'cancelado') {
                return;
            }

            $fechaHoraInicio = Carbon::parse($this->fecha_hora_inicio);
            $oneHourFromNow = now()->addHour();

            $turno = $this->route('turno');
            $fechaCambiada = ! $turno || $turno->fecha_hora_inicio->format('Y-m-d H:i') !== $fechaHoraInicio->format('Y-m-d H:i');

            if ($fechaCambiada && $fechaHoraInicio->isBefore($oneHourFromNow)) {
                $validator->errors()->add(
                    'fecha_hora_inicio',
                    'Los turnos solo pueden reprogramarse hacia un horario con una anticipación mínima de 1 hora.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'item_catalogo_id.required' => 'Debe seleccionar un servicio o combo.',
            'fecha_hora_inicio.required' => 'La fecha y hora del turno es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
