<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterIncidentsRequest extends FormRequest
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
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'severity' => ['nullable', Rule::enum(IncidentSeverity::class)],
            'status' => ['nullable', Rule::enum(IncidentStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'project_id.integer' => 'O filtro de projeto é inválido.',
            'project_id.exists' => 'O projeto usado no filtro não existe.',
            'severity.enum' => 'O filtro de severidade é inválido.',
            'status.enum' => 'O filtro de status é inválido.',
        ];
    }

    /** @return array{project_id: int|null, severity: string|null, status: string|null} */
    public function filters(): array
    {
        return [
            'project_id' => $this->filled('project_id') ? $this->integer('project_id') : null,
            'severity' => $this->filled('severity') ? $this->string('severity')->toString() : null,
            'status' => $this->filled('status') ? $this->string('status')->toString() : null,
        ];
    }
}
