<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'unique:clients,phone'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Este número de teléfono ya se encuentra registrado.',
            'phone.regex' => 'El formato del teléfono es inválido. Solo se permiten números, espacios y los símbolos + - ( )',
            'phone.min' => 'El teléfono debe tener al menos 8 caracteres.',
        ];
    }
}
