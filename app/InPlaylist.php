<?php

namespace App;

use App\Playback\Track;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class InPlaylist implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return tap($this, fn () => $this->data = $data);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (($playlist = Arr::get($this->data, 'playlist')) === null) {
            return;
        }
        if (Track::query()->find($value)?->playlists()?->find($playlist) === null) {
            $fail("Track {$value} not found in Playlist {$playlist}.");
        }
    }
}
