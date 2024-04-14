export interface GetPlaylistResponse {
  images: PlaylistImage[];
  tracks: PlaylistTracks;
  name: string;
}

export interface PlaylistImage {
  url: string;
}

export interface PlaylistTracks {
  items: PlaylistItem[];
}

export interface PlaylistItem {
  track: PlaylistTrack;
  added_by: {
    id: string;
  };
}

export interface PlaylistTrack {
  artists: PlaylistArtist[];
  album: PlaylistAlbum;
  name: string;
  id: string;
}

export interface PlaylistAlbum {
  images: Image[];
  name: string;
}

export interface PlaylistArtist {
  name: string;
}

export interface GetCurrentlyPlayingResponse {
  timestamp: number;
  context: {
    external_urls: ExternalUrls;
    href: string;
    type: string;
    uri: string;
  };
  progress_ms: number;
  item: CurrentlyPlayingItem;
  currently_playing_type: string;
  actions: {
    disallows: {
      resuming: boolean;
    };
  };
  is_playing: boolean;
}

export interface ExternalUrls {
  spotify: string;
}

export interface CurrentlyPlayingItem {
  album: CurrentlyPlayingAlbum;
  artists: CurrentlyPlayingArtist[];
  available_markets: string[];
  disc_number: number;
  duration_ms: number;
  explicit: boolean;
  external_ids: {
    isrc: string;
  };
  external_urls: ExternalUrls;
  href: string;
  id: string;
  is_local: boolean;
  name: string;
  popularity: number;
  preview_url: string;
  track_number: number;
  type: string;
  uri: string;
}

export interface CurrentlyPlayingAlbum {
  album_type: string;
  artists: CurrentlyPlayingArtist[];
  available_markets: string[];
  external_urls: ExternalUrls;
  href: string;
  id: string;
  images: Image[];
  name: string;
  release_date: Date;
  release_date_precision: string;
  total_tracks: number;
  type: string;
  uri: string;
}

export interface CurrentlyPlayingArtist {
  external_urls: ExternalUrls;
  href: string;
  id: string;
  name: string;
  type: string;
  uri: string;
}

export interface Image {
  height: number;
  url: string;
  width: number;
}
