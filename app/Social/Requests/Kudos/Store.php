<?php

namespace App\Social\Requests\Kudos;

use App\InPlaylist;
use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'track' => [
                'sometimes',
                'required_with:playlist',
                'string',
                'exists:spotify_tracks,id',
                new InPlaylist,
            ],
            'playlist' => [
                'sometimes',
                'required',
                'string',
                'exists:spotify_playlists,id',
            ],
            'for' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
