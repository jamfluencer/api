<?php

namespace App\Playback\Requests\Jam;

use Illuminate\Foundation\Http\FormRequest;

class Start extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playlist' => 'required|string',
            'jam' => 'required|string|url:https',
        ];
    }
}
