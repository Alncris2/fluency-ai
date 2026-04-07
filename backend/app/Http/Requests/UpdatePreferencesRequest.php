<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
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
            'preferred_name' => ['required', 'string'],
            'goal' => ['required', 'string', 'in:viagem,trabalho,hobby'],
            'english_level' => ['required', 'string', 'in:nunca_estudei,basico,intermediario,avancado'],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['string', 'in:series,musica,esportes,tecnologia,viagem'],
            'availability' => ['required', 'array'],
            'availability.days' => ['required', 'array', 'min:1'],
            'availability.days.*' => ['string', 'in:seg,ter,qua,qui,sex,sab,dom'],
            'availability.time_of_day' => ['required', 'array', 'min:1'],
            'availability.time_of_day.*' => ['string', 'in:manha,tarde,noite'],
        ];
    }
}
