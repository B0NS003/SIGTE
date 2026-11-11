<?php

namespace App\Http\Requests\Flujo;

use Illuminate\Foundation\Http\FormRequest;

class GuardarRecepcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'tipo_conjunto_id' => ['required', 'integer', 'exists:tipo_conjunto,id'],
            'ficha_id' => ['required', 'integer', 'exists:fichas,id'],
            'quien_entrega' => ['required', 'string', 'max:150'],
            'obs' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'servicio_id.required' => 'Selecciona el servicio de origen.',
            'tipo_conjunto_id.required' => 'Selecciona el tipo de conjunto.',
            'ficha_id.required' => 'Selecciona una caja del catálogo.',
            'quien_entrega.required' => 'Escribe el nombre de quien entrega el material.',
        ];
    }
}
