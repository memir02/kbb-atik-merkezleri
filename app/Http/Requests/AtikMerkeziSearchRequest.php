<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtikMerkeziSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Herkes arama yapabilir
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|min:2|max:100',
            'filter' => 'nullable|array',
            'filter.*' => 'string|in:mobil,plastik,metal,cam,kagit,pil,bitkisel,atıkcam,tekstil,gecici,ilac,sinif1,inert,hafriyat',
            'q' => 'nullable|string|min:2|max:100', // API için
            'filters' => 'nullable|array', // API için
            'filters.*' => 'string|in:mobil,plastik,metal,cam,kagit,pil,bitkisel,atıkcam,tekstil,gecici,ilac,sinif1,inert,hafriyat',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'search.min' => 'Arama terimi en az 2 karakter olmalıdır.',
            'search.max' => 'Arama terimi en fazla 100 karakter olabilir.',
            'filter.*.in' => 'Geçersiz filtre değeri.',
            'q.min' => 'Arama terimi en az 2 karakter olmalıdır.',
            'q.max' => 'Arama terimi en fazla 100 karakter olabilir.',
            'filters.*.in' => 'Geçersiz filtre değeri.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'search' => 'arama terimi',
            'filter' => 'filtre',
            'q' => 'arama terimi',
            'filters' => 'filtreler',
        ];
    }
} 