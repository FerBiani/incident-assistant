<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StreamIncidentConversationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('message') && is_string($this->input('message'))) {
            $this->merge(['message' => trim($this->input('message'))]);
        }
    }

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
            'message' => [
                'required_without:decisions',
                Rule::prohibitedIf(fn (): bool => $this->filled('decisions')),
                'string',
                'max:10000',
            ],
            'decisions' => [
                'required_without:message',
                Rule::prohibitedIf(fn (): bool => $this->filled('message')),
                'array',
                'min:1',
            ],
            'decisions.*' => ['required', 'array:action'],
            'decisions.*.action' => ['required', 'string', 'in:approve,reject'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'message.required_without' => 'Escreva uma mensagem para continuar a investigação.',
            'message.prohibited' => 'Envie uma mensagem ou decisões de aprovação, nunca ambos.',
            'message.string' => 'A mensagem deve ser um texto.',
            'decisions.required_without' => 'Informe as decisões das ações pendentes.',
            'decisions.prohibited' => 'Envie decisões de aprovação ou uma mensagem, nunca ambos.',
            'decisions.array' => 'As decisões informadas são inválidas.',
            'decisions.min' => 'Informe ao menos uma decisão.',
            'decisions.*.action.in' => 'Cada decisão deve aprovar ou rejeitar a ação.',
        ];
    }
}
