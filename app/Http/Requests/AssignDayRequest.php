<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignDayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'selectedUserId.required' => 'required|exists:usuarios,id',
            'selectedDate.after_or_equal' => 'required|date|after_or_equal:today'
        ];
    }

    public function messages() 
    {
        return [
            'selectedUserId.required' => 'Selecione um usuário para atribuir o dia.',
            'selectedUserId.exists' => 'O usuário selecionado não existe.',
            'selectedDate.required' => 'A data é obrigatória.',
            'selectedDate.date' => 'A data deve ser válida',
            'selectedDate.after_or_equal' => 'Não é possível atribuir dias no passado.'
        ];
    }
}
