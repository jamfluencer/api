<?php

namespace App\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Search extends FormRequest
{
    public function rules(): array
    {
        return [
            'track' => 'sometimes|required|string',
        ];
    }
}
