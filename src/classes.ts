import querystring from 'node:querystring';
import { GetPlaylistResponse, GetCurrentlyPlayingResponse } from './types';

export interface Playlist {
  name: string;
  image: string;
  tracks: TrackWithAddedBy[];
}

export interface Track {
  id: string;
  artist: string;
  song: string;
  album: string;
  image: string;
}

interface TrackWithAddedBy extends Track {
  addedBy: string;
}

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

    const data = await response.json();

    this.accessToken = data.access_token;
    this.refreshToken = data.refresh_token;
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

    const data = await response.json();

    this.accessToken = data.access_token;
    this.refreshToken = data.refresh_token;
    this.resetExpiry();
  }

  async getCurrentlyPlaying(): Promise<Track | null> {
    if (!this.accessToken) throw new Error('Not authorized');

    const response = await fetch(
      'https://api.spotify.com/v1/me/player/currently-playing',
      {
        headers: {
          Authorization: 'Bearer ' + this.accessToken,
        },
      }
    );

    if (response.status === 204) return null;

    const data =
      await (response.json() as Promise<GetCurrentlyPlayingResponse>);

    return {
      id: data.item.id,
      artist: data.item.artists.map((artist) => artist.name).join(', '),
      song: data.item.name,
      album: data.item.album.name,
      image: data.item.album.images[0].url,
    };
  }

  async getPlaylist(id: string): Promise<Playlist> {
    if (!this.accessToken) throw new Error('Not authorized');

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
    const data = await (response.json() as Promise<GetPlaylistResponse>);

    return {
      name: data.name,
      image: data.images[0].url,
      tracks: data.tracks.items.map((item) => ({
        id: item.track.id,
        artist: item.track.artists.map((artist) => artist.name).join(', '),
        song: item.track.name,
        album: item.track.album.name,
        image: item.track.album.images[2].url,
        addedBy: item.added_by.id,
      })),
    };
  }
}
