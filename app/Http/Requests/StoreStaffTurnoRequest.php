<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffTurnoRequest extends FormRequest
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
            'client_id' => ['nullable', 'exists:clients,id'],
            'first_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
            'last_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
            'phone' => ['required_without:client_id', 'nullable', 'string', 'max:255', 'unique:clients,phone'],
            'item_catalogo_id' => ['required', 'exists:item_catalogos,id'],
            'fecha_hora_inicio' => ['required', 'date', 'after_or_equal:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required_without' => 'El nombre es obligatorio si no selecciona un cliente.',
            'last_name.required_without' => 'El apellido es obligatorio si no selecciona un cliente.',
            'phone.required_without' => 'El teléfono es obligatorio si no selecciona un cliente.',
            'phone.unique' => 'Este teléfono ya está registrado en otro cliente.',
            'item_catalogo_id.required' => 'Debe seleccionar un servicio o combo.',
            'fecha_hora_inicio.after_or_equal' => 'La fecha y hora del turno no puede estar en el pasado.',
        ];
    }
}
