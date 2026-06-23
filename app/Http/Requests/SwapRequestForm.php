<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SwapRequestForm extends FormRequest
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
            'from_user_id' => 'required|exists:usuarios,id', 
            'to_user_id' => 'required|exists:usuarios, id|different:from_user_id', 
            'from_dish_day_id' => 'required|exists:dish_schedules, id', 
            'to_dish_day_id' => 'nullable|exists:dish_schedules, id', 
            'status' => 'required|in:pending,accepted, rejected', 
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'from_dish_day_id.required' => 'Dia de origem não encontrado.',
            'to_user_id.required' => 'Selecione com quem quer trocar.',
            'to_user_id.different' => 'Não podes trocar contigo mesma(o).',
            'to_dish_day_id.required' => 'Selecione o dia do outro usuário.',
            'notes.max' => 'A justificativa não pode ter mais de 500 caracteres.'
        ];
    }
}
