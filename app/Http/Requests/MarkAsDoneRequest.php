<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class MarkAsDoneRequest extends FormRequest
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ];
    }

    public function messages()
    {
        return [
            'photo.image' => 'O ficheiro deve ser uma imagem.',
            'photo.mimes' => 'Apenas são permitidos ficheiros JPEG, PNG, GIF ou WEBP',
            'photo.max' => 'A foto não pode ter mais de 5MB.'
        ];
    }
}
