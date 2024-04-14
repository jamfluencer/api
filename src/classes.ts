import querystring from 'node:querystring';

export class SpotifyApi {
  private clientId: string;
  private clientSecret: string;
  private redirectUri: string;
  private accessToken: string;
  private refreshToken: string;
  private accessTokenExpiry: number;

  constructor(clientId: string, clientSecret: string, redirectUri: string) {
    this.clientId = clientId;
    this.clientSecret = clientSecret;
    this.redirectUri = redirectUri;
  }

  resetExpiry() {
    this.accessTokenExpiry = Date.now() + 3500 * 1000;
  }

  redirectUrl() {
    const scope = 'user-read-currently-playing playlist-read-private';
    return (
      'https://accounts.spotify.com/authorize?' +
      querystring.stringify({
        response_type: 'code',
        client_id: this.clientId,
        redirect_uri: this.redirectUri,
        scope,
      })
    );
  }

  isAuthorized() {
    return !!this.accessToken;
  }

  isExpired() {
    if (!this.accessTokenExpiry) return true;
    return Date.now() >= this.accessTokenExpiry;
  }

  async getAccessToken(code: string) {
    const response = await fetch('https://accounts.spotify.com/api/token', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        Authorization: 'Basic ' + btoa(this.clientId + ':' + this.clientSecret),
      },
      body: new URLSearchParams({
        code,
        redirect_uri: this.redirectUri,
        grant_type: 'authorization_code',
      }),
    });

    if (response.status !== 200) throw new Error('Invalid code');

    const body = await response.json();

    this.accessToken = body.access_token;
    this.refreshToken = body.refresh_token;
    this.resetExpiry();
  }

  async refreshAccessToken() {
    if (!this.refreshToken) throw new Error('No refresh token');

    const response = await fetch('https://accounts.spotify.com/api/token', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        Authorization: 'Basic ' + btoa(this.clientId + ':' + this.clientSecret),
      },
      body: new URLSearchParams({
        grant_type: 'refresh_token',
        refresh_token: this.refreshToken,
      }),
    });

    const body = await response.json();

    this.accessToken = body.access_token;
    this.refreshToken = body.refresh_token;
    this.resetExpiry();
  }

  async getCurrentlyPlaying() {
    if (!this.accessToken) throw new Error('Not authorized');

    const response = await fetch(
      'https://api.spotify.com/v1/me/player/currently-playing',
      {
        headers: {
          Authorization: 'Bearer ' + this.accessToken,
        },
      }
    );

    const body = await response.json();

    return {
      id: body.item.id,
      artist: body.item.artists[0].name,
      song: body.item.name,
      album: body.item.album.name,
      image: body.item.album.images[0].url,
    };
  }

  async getPlaylist(id: string) {
    const fields =
      'name,images.url,tracks.items(added_by.id,track(name,id,album(name,images),artists(name)))';

    const response = await fetch(
      `https://api.spotify.com/v1/playlists/${id}?fields=${fields}`,
      {
        headers: {
          Authorization: 'Bearer ' + this.accessToken,
        },
      }
    );

    return await response.json();
  }
}
