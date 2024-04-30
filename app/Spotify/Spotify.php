<?php

namespace App\Spotify;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Spotify
{
    private readonly PendingRequest $http;

    public function __construct(
        private readonly string $id,
        private readonly string $secret
    ) {
    }

    public function authUrl(string $redirectPath): string
    {
        return URL::query(
            'https://accounts.spotify.com/authorize',
            [
                'response_type' => 'code',
                'client_id' => $this->id,
                'scope' => 'user-read-currently-playing user-read-playback-state playlist-read-private',
                'redirect_uri' => URL::to($redirectPath),
                'state' => Str::random(),
            ]
        );
    }

    public function accessToken(string $authorizationCode): AccessToken
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode("{$this->id}:{$this->secret}"),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->asForm()
            ->post(
                'https://accounts.spotify.com/api/token',
                [
                    'grant_type' => 'authorization_code',
                    'code' => $authorizationCode,
                    'redirect_uri' => URL::to('https://jamfluencer.app/auth/spotify/callback'),
                ]
            );

        return new AccessToken(
            token: $response->json('access_token'),
            refresh: $response->json('refresh_token'),
            expiry: $response->json('expires_in'),
            scopes: $response->json('scope')
        );
    }

    public function refreshToken(AccessToken $token): AccessToken
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode("{$this->id}:{$this->secret}"),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->asForm()
            ->post(
                'https://accounts.spotify.com/api/token',
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->refresh,
                ]
            );

        return new AccessToken(
            token: $response->json('access_token'),
            refresh: $response->json('refresh_token', $token->refresh),
            expiry: $response->json('expires_in'),
            scopes: $response->json('scope')
        );
    }

    public function currentlyPlaying(): ?Track
    {
        $response = $this->http->get('/v1/me/player/currently-playing');

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return null;
        }

        return Track::fromSpotify($response->json('item', []));
    }

    public function playlist(): Playlist
    {
        return new Playlist;
    }

    public function queue(): Queue
    {
        return new Queue;
    }

    public function setToken(AccessToken $token): self
    {
        $this->client()->replaceHeaders(['Authorization' => "Bearer {$token->token}"]);

        return $this;
    }

    private function client(): PendingRequest
    {
        return $this->http ??= Http::baseUrl('https://api.spotify.com')
            ->throw();
    }
}
