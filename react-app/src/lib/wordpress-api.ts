import { useQuery } from '@tanstack/react-query';

const getWPApiBase = () => {
  const apiUrl = window.opusData?.apiUrl || '';
  // Strip '/lovable/v1' to get the base WP REST URL
  return apiUrl.replace(/\/lovable\/v1$/, '');
};

export interface WPGallery {
  id: number;
  title: { rendered: string };
  content: { rendered: string };
  featured_media: number;
  meta_fields?: {
    image?: string;
    description?: string;
  };
  _embedded?: {
    'wp:featuredmedia'?: Array<{ source_url: string }>;
  };
}

export interface WPArtist {
  id: number;
  title: { rendered: string };
  content: { rendered: string };
  meta_fields?: {
    image?: string;
    speciality?: string;
  };
  _embedded?: {
    'wp:featuredmedia'?: Array<{ source_url: string }>;
  };
}

export function useWPGalleries() {
  return useQuery({
    queryKey: ['wp-galleries'],
    queryFn: async (): Promise<WPGallery[]> => {
      const response = await fetch(`${getWPApiBase()}/wp/v2/opus_gallery?per_page=100&_embed`);
      if (!response.ok) throw new Error('Failed to fetch galleries');
      return response.json();
    },
    enabled: !!window.opusData,
  });
}

export function useWPArtists() {
  return useQuery({
    queryKey: ['wp-artists'],
    queryFn: async (): Promise<WPArtist[]> => {
      const response = await fetch(`${getWPApiBase()}/wp/v2/opus_artist?per_page=100&_embed`);
      if (!response.ok) throw new Error('Failed to fetch artists');
      return response.json();
    },
    enabled: !!window.opusData,
  });
}

export function wpGalleryToLocal(gallery: WPGallery) {
  const image = gallery.meta_fields?.image ||
    gallery._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';

  return {
    id: String(gallery.id),
    title: gallery.title.rendered,
    description: gallery.meta_fields?.description || '',
    image,
  };
}

export function wpArtistToLocal(artist: WPArtist) {
  const image = artist.meta_fields?.image ||
    artist._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';

  return {
    id: String(artist.id),
    name: artist.title.rendered,
    bio: artist.content.rendered,
    image,
    speciality: artist.meta_fields?.speciality || '',
  };
}
