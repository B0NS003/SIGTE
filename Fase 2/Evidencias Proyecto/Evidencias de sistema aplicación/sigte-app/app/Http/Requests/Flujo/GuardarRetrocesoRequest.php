<?php

namespace App\Http\Requests\Flujo;

use Illuminate\Foundation\Http\FormRequest;

class GuardarRetrocesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'caja' => trim((string) $this->input('caja', '')),
            'motivo' => trim((string) $this->input('motivo', '')),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'caja' => ['required', 'string', 'max:40'],
            'motivo' => ['required', 'string', 'max:180'],
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
            'caja.required' => 'Elige la caja que vas a corregir.',
            'motivo.required' => 'Registra el motivo del retroceso.',
        ];
    }
}
