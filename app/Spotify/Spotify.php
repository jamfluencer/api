<?php

namespace App\Spotify;

use App\Spotify\Authentication\AccessToken;
use App\Spotify\Authentication\ClientToken;
use App\Spotify\Authentication\RefreshToken;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class Spotify
{
    private const BASE_URL = 'https://api.spotify.com';

    private readonly PendingRequest $http;

    public function __construct(
        private readonly string $id,
        private readonly string $secret
    ) {}

    public function authUrl(string $redirectPath): string
    {
        $scopes = [
            'user-read-currently-playing',
            'user-read-playback-state',
            'playlist-read-private',
            'playlist-read-collaborative',
            'user-modify-playback-state',
        ];

        return URL::query(
            'https://accounts.spotify.com/authorize',
            [
                'response_type' => 'code',
                'client_id' => $this->id,
                'scope' => implode(' ', $scopes),
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
                    'redirect_uri' => URL::to(config('spotify.redirect_uri')),
                ]
            );

        return new AccessToken(
            token: $response->json('access_token'),
            expiry: $response->json('expires_in'),
            refresh: $response->json('refresh_token'),
            scopes: $response->json('scope')
        );
    }

    public function refreshToken(RefreshToken $token): AccessToken
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
                    'refresh_token' => (string) $token,
                ]
            );

        return new AccessToken(
            token: $response->json('access_token'),
            expiry: $response->json('expires_in'),
            refresh: $response->json('refresh_token', (string) $token),
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

    public function playlist(string $id, bool $complete = false): ?Playlist
    {
        $response = $this->http->get("/v1/playlists/{$id}?fields=id,snapshot_id,name,external_urls(spotify),images,collaborative,tracks(total,next,items(added_by,track(id,name,artists,duration_ms,external_urls.spotify,album(id,uri,name,images,external_urls(spotify)))))");

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return null;
        }

        $playlist = Playlist::fromSpotify($response->json());
        while ($playlist->next && $complete) {
            $additional = $this->http->get(
                Str::after(
                    $this->trackUrlFromPlaylistUrl($playlist->next),
                    self::BASE_URL)
            );
            $playlist = $playlist->extend(
                array_map(fn (array $track) => Track::fromSpotify($track), $additional->json('items', [])),
                $additional->json('next')
            );
        }

        return $playlist;
    }

    private function trackUrlFromPlaylistUrl(string $playlistUrl): string
    {
        $components = parse_url($playlistUrl);
        parse_str($components['query'] ?? '', $query);
        $query['fields'] = 'next,items(added_by,track(id,name,artists,duration_ms,album(id,name,images)))';

        return URL::fromComponents(array_merge($components, ['query' => http_build_query($query)]));
    }

    public function profile(?string $id = null): Profile
    {
        return new Profile(...$this->http->get(
            $id === null ? '/v1/me' : "/v1/users/{$id}"
        )->json());
    }

    public function queue(): ?Queue
    {
        $response = $this->http->get('/v1/me/player/queue');

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return null;
        }

        return Queue::fromSpotify($response->json());
    }

    public function withClientCredentials(): self
    {
        $this->client()->replaceHeaders(['Authorization' => 'Bearer '.$this->clientToken()]);

        return $this;
    }

    protected function clientToken(): ClientToken
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode("{$this->id}:{$this->secret}"),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->asForm()
            ->post(
                'https://accounts.spotify.com/api/token',
                [
                    'grant_type' => 'client_credentials',
                ]
            );

        return new ClientToken(
            $response->json('access_token'),
            $response->json('expires_in')
        );
    }

    public function setToken(AccessToken|ClientToken $token): self
    {
        if ($token->expired()) {
            throw new RuntimeException('Token expired');
        }

        $this->client()->replaceHeaders(['Authorization' => "Bearer {$token->token}"]);

        return $this;
    }

    public function play(?string $uri = null): Track
    {
        $this->http->put('/v1/me/player/play', array_filter([
            'context_uri' => $uri,
        ]));
        usleep(500_000);

        return $this->currentlyPlaying();
    }

    public function pause(): bool
    {
        return $this->http->put('/v1/me/player/pause')->successful();
    }

    private function client(): PendingRequest
    {
        return $this->http ??= Http::baseUrl(self::BASE_URL)
            ->throw();
    }
}
