<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
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
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logs' => ['nullable', 'string'],
            'severity' => ['required', Rule::enum(IncidentSeverity::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Selecione o projeto do incidente.',
            'project_id.integer' => 'Selecione um projeto válido.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'title.required' => 'Informe o título do incidente.',
            'title.string' => 'O título do incidente deve ser um texto.',
            'title.max' => 'O título do incidente deve ter no máximo 255 caracteres.',
            'description.string' => 'A descrição do incidente deve ser um texto.',
            'logs.string' => 'Os logs devem ser um texto.',
            'severity.required' => 'Selecione a severidade do incidente.',
            'severity.enum' => 'Selecione uma severidade válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => $this->filled('description') ? $this->input('description') : null,
            'logs' => $this->filled('logs') ? $this->input('logs') : null,
        ]);
    }
}
