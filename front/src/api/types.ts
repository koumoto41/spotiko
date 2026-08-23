export interface User {
  id: number
  name: string
  email: string
}

export interface TopArtistItem {
  id: number
  target_date: string
  artist_id: number
  point: number
  popularity: number
  spotify_id: string
  name: string
  url: string
}

export interface TopTrackItem {
  album_name: string
  track_name: string
  artist_name: string
  artist_url: string
  track_url: string
}

export interface Rankings {
  artists: {
    aggregate_date: string | null
    items: TopArtistItem[]
  }
  tracks: {
    aggregate_date: string | null
    items: TopTrackItem[]
  }
}

export interface MyAlbumItem {
  album_name: string
  artist_name: string
  img_640: string
  img_300: string
  img_64: string
  url: string
  created_at: string
}

export interface StockList {
  count: number
  items: MyAlbumItem[]
  page: number
  limit: number
}

export interface SpotifySearchAlbumItem {
  album_name: string
  album_spotify_id: string
  release_date: string
  type: string
  artist_name: string
  artist_spotify_id: string
  image: string
  is_register: boolean
}

export interface RegisterResult {
  processing: boolean
  message: string
}
