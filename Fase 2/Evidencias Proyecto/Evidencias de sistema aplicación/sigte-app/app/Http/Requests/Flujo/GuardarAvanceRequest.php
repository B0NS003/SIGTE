<?php

namespace App\Http\Requests\Flujo;

use Illuminate\Foundation\Http\FormRequest;

class GuardarAvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'caja' => trim((string) $this->input('caja', '')),
            'etapa_destino' => trim((string) $this->input('etapa_destino', '')),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'caja' => ['required', 'string', 'max:40'],
            'etapa_destino' => ['required', 'string', 'max:40'],
            'q' => ['nullable', 'string', 'max:120'],
            'etapa' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'caja.required' => 'Elige la caja que vas a pasar.',
            'etapa_destino.required' => 'Falta la etapa a la que pasa la caja.',
        ];
    }
}
