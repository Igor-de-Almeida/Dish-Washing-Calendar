<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Override;

class GenerateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->tipo === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scaleType' => 'required|in:weekly_rotation, fixed_days',
            'startMonth' => 'required|date_format:Y-m|after_or_equal:' . now()->format('Y-m')
        ];
    }

    public function messages()
    {
        return [
            'startMonth.after_or_equal' => 'O mês de início não pode ser no passado.',
            'startMonth.required' => 'Selecione o mês de início.',
            'scaleType.in' => 'Tipo de escala inválido.',
            'scaleType.required' => 'Selecione o tipo de escala.'
        ];
    }
}
