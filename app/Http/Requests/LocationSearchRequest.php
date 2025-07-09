<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Herkes konum bazlı arama yapabilir
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'limit' => 'nullable|integer|min:1|max:50',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'lat.required' => 'Enlem bilgisi gereklidir.',
            'lat.numeric' => 'Enlem bilgisi sayısal olmalıdır.',
            'lat.between' => 'Enlem -90 ile 90 arasında olmalıdır.',
            'lon.required' => 'Boylam bilgisi gereklidir.',
            'lon.numeric' => 'Boylam bilgisi sayısal olmalıdır.',
            'lon.between' => 'Boylam -180 ile 180 arasında olmalıdır.',
            'limit.integer' => 'Limit değeri tam sayı olmalıdır.',
            'limit.min' => 'Limit en az 1 olmalıdır.',
            'limit.max' => 'Limit en fazla 50 olabilir.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'lat' => 'enlem',
            'lon' => 'boylam',
            'limit' => 'limit',
        ];
    }
} 