<?php

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

function hasAggregates(AssertableJson $json): AssertableJson
{
    return $json->has('mean')
        ->has('maximum')
        ->has('minimum');
}

it('returns expected types', function () {
    $ryan = User::factory()->withSpotify()->create();
    $marshall = User::factory()->withSpotify()->create();
    Playlist::factory()->create()->tracks()->sync([
        (Track::factory()->create())->id => ['added_by' => $ryan->spotifyAccounts()->first()->id],
        (Track::factory()->create())->id => ['added_by' => $marshall->spotifyAccounts()->first()->id],
        (Track::factory()->create())->id => ['added_by' => $marshall->id],
    ]);
    DB::insert('insert into wrapped_codes (code, user_id) values (?, ?)',
        ['wrapped', $marshall->id]);
    test()->get('v1/wrapped/wrapped')
        ->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('jam', fn (AssertableJson $jam) => $jam
                ->whereType('playlists', 'integer')
                ->whereType('tracks', 'integer')
                ->whereType('contributors', 'integer')
                ->whereType('duration', 'string'))
            ->has('playlists', fn (AssertableJson $playlist) => $playlist
                ->whereType('count', 'integer')
                ->has('tracks', fn (AssertableJson $tracks) => test()->hasAggregates($tracks))
                ->has('duration', fn (AssertableJson $duration) => test()->hasAggregates($duration)))
            ->has('tracks', fn (AssertableJson $tracks) => $tracks
                ->has('duration', fn (AssertableJson $duration) => test()->hasAggregates($duration))
                ->whereType('count', 'integer')
                ->has('occurrence', fn (AssertableJson $occurrence) => $occurrence
                    ->has('details')
                    ->has('playlists')))
            ->has('you', fn (AssertableJson $you) => $you
                ->has('social')
                ->has('jam'))
            ->has('social', fn (AssertableJson $social) => $social
                ->has('track')
                ->has('contributor')
                ->has('playlist'))
        );
});
