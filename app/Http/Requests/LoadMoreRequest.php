<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadMoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Herkes daha fazla veri yükleyebilir
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
            'ids' => 'nullable|array',
            'ids.*' => 'integer|exists:atik_merkezleri,id',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'offset.integer' => 'Offset değeri tam sayı olmalıdır.',
            'offset.min' => 'Offset negatif olamaz.',
            'limit.integer' => 'Limit değeri tam sayı olmalıdır.',
            'limit.min' => 'Limit en az 1 olmalıdır.',
            'limit.max' => 'Limit en fazla 50 olabilir.',
            'ids.array' => 'ID listesi dizi formatında olmalıdır.',
            'ids.*.integer' => 'Her ID tam sayı olmalıdır.',
            'ids.*.exists' => 'Belirtilen ID geçerli değil.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'offset' => 'başlangıç noktası',
            'limit' => 'limit',
            'ids' => 'ID listesi',
        ];
    }
} 