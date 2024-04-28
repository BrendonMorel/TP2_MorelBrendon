<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilmRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'release_year' => 'required|integer|digits:4|min:1895', // Premier film en 1895
            'length' => 'required|integer|min:0',
            'description' => 'required|string',
            'rating' => 'required|string|max:5',
            'special_features' => 'required|string|max:200',
            'image' => 'required|string|max:40',
            'language_id' => 'required|integer|exists:languages,id'
        ];
    }
}
