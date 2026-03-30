# OPUS Gallery → WordPress Migration Plan

Complete step-by-step guide to migrate your Lovable app to WordPress.

## Current State Analysis

✅ **React App:** `E:\projects\opus-gallery` (Lovable build)
✅ **WordPress Theme:** `E:\projects\gallery-template\theme`
✅ **WordPress Site:** `E:\websites\gallery-template`
✅ **Junction:** WordPress themes → theme directory

**Data Structure:**
- Artists: 8 artists (Floyd, John, Joost, Loes, Milan, Peter R, Peter C, Raymon)
- Artworks: ~50+ pieces across multiple galleries
- Galleries: Multiple collections (Archeology, Flux, Origin, etc.)
- Static images in `src/assets/`

## Migration Steps (60-90 minutes)

### Phase 1: Install Dependencies (5 min)

```bash
cd E:/projects/opus-gallery

# Install WordPress API dependencies
npm install @tanstack/react-query axios

# Verify installation
npm list @tanstack/react-query axios
```

### Phase 2: Create WordPress API Layer (15 min)

Create `src/lib/wordpress-api.ts`:

```typescript
import axios from 'axios';
import { useQuery } from '@tanstack/react-query';

// Get WordPress API URL
const getApiUrl = () => {
  if (typeof window !== 'undefined' && (window as any).wpData?.apiUrl) {
    return (window as any).wpData.apiUrl;
  }
  // Fallback for development
  return 'http://localhost/gallery-template/wp-json/opus/v1';
};

// API client
const api = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
  },
});

// WordPress API Types (match your existing interfaces)
export interface WPArtist {
  id: number;
  name: string;
  slug: string;
  bio: string;
  photo: string;
  nationality: string;
  birth_year: string;
  website: string;
  date: string;
}

export interface WPArtwork {
  id: number;
  title: string;
  slug: string;
  description: string;
  image: string;
  thumbnail: string;
  artist_id: number;
  gallery_id: number;
  price: string;
  year: string;
  medium: string;
  dimensions: string;
  status: 'available' | 'sold' | 'on-loan';
  date: string;
}

export interface WPGallery {
  id: number;
  title: string;
  slug: string;
  description: string;
  featured_image: string;
  thumbnail: string;
  date: string;
}

export interface WPExhibition {
  id: number;
  title: string;
  slug: string;
  description: string;
  featured_image: string;
  start_date: string;
  end_date: string;
  location: string;
  status: 'upcoming' | 'current' | 'past';
  date: string;
}

// React Query Hooks
export function useWPGalleries() {
  return useQuery<WPGallery[]>({
    queryKey: ['wp-galleries'],
    queryFn: async () => {
      const { data } = await api.get('/galleries');
      return data;
    },
  });
}

export function useWPGallery(id: number | string) {
  return useQuery<WPGallery>({
    queryKey: ['wp-gallery', id],
    queryFn: async () => {
      const { data } = await api.get(`/galleries/${id}`);
      return data;
    },
    enabled: !!id,
  });
}

export function useWPArtworks(galleryId?: number) {
  return useQuery<WPArtwork[]>({
    queryKey: ['wp-artworks', galleryId],
    queryFn: async () => {
      const params = galleryId ? `?gallery_id=${galleryId}` : '';
      const { data } = await api.get(`/artworks${params}`);
      return data;
    },
  });
}

export function useWPArtwork(id: number | string) {
  return useQuery<WPArtwork>({
    queryKey: ['wp-artwork', id],
    queryFn: async () => {
      const { data } = await api.get(`/artworks/${id}`);
      return data;
    },
    enabled: !!id,
  });
}

export function useWPArtists() {
  return useQuery<WPArtist[]>({
    queryKey: ['wp-artists'],
    queryFn: async () => {
      const { data } = await api.get('/artists');
      return data;
    },
  });
}

export function useWPExhibitions() {
  return useQuery<WPExhibition[]>({
    queryKey: ['wp-exhibitions'],
    queryFn: async () => {
      const { data } = await api.get('/exhibitions');
      return data;
    },
  });
}

// Adapter functions to convert WordPress data to your existing format
export function wpArtistToLocal(wpArtist: WPArtist) {
  return {
    id: wpArtist.id.toString(),
    name: wpArtist.name,
    bio: stripHtml(wpArtist.bio),
    image: wpArtist.photo || '',
    nationality: wpArtist.nationality || '',
    speciality: '', // You can add this as custom field in WordPress
  };
}

export function wpArtworkToLocal(wpArtwork: WPArtwork) {
  return {
    id: wpArtwork.id.toString(),
    title: wpArtwork.title,
    artist: '', // Need to fetch artist name separately
    artistId: wpArtwork.artist_id.toString(),
    year: wpArtwork.year || '',
    medium: wpArtwork.medium || '',
    dimensions: wpArtwork.dimensions || '',
    shortDescription: stripHtml(wpArtwork.description).substring(0, 200),
    extendedDescription: stripHtml(wpArtwork.description),
    story: stripHtml(wpArtwork.description),
    keywords: [],
    category: 'Painting' as const,
    images: [wpArtwork.image],
    galleryId: wpArtwork.gallery_id.toString(),
    artRevisionistUrl: '',
    price: wpArtwork.price ? parseFloat(wpArtwork.price) : undefined,
    priceLabel: wpArtwork.price ? `€${wpArtwork.price}` : undefined,
    sold: wpArtwork.status === 'sold',
    location: wpArtwork.status === 'available' ? 'gallery' as const : 'depot' as const,
  };
}

export function wpGalleryToLocal(wpGallery: WPGallery) {
  return {
    id: wpGallery.id.toString(),
    name: wpGallery.title,
    description: stripHtml(wpGallery.description),
    featured: wpGallery.featured_image || '',
  };
}

// Utility: Strip HTML tags
function stripHtml(html: string): string {
  if (!html) return '';
  const div = document.createElement('div');
  div.innerHTML = html;
  return div.textContent || div.innerText || '';
}
```

### Phase 3: Update Vite Config (5 min)

Edit `E:\projects\opus-gallery\vite.config.ts`:

```typescript
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8080,
    hmr: {
      overlay: false,
    },
    // CORS for WordPress dev
    cors: true,
  },
  plugins: [react()].filter(Boolean),
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  build: {
    // Output to WordPress theme
    outDir: '../gallery-template/theme/dist',
    emptyOutDir: true,
    // Generate manifest for WordPress
    manifest: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'index.html'),
      },
    },
  },
}));
```

### Phase 4: Create Data Adapter (10 min)

Create `src/data/wordpress-adapter.ts`:

```typescript
import { useWPGalleries, useWPArtworks, useWPArtists, wpArtistToLocal, wpArtworkToLocal, wpGalleryToLocal } from '@/lib/wordpress-api';
import type { Artist, ArtPiece, Gallery } from './collections';

// Hook to get all artists (replaces static artists array)
export function useArtists() {
  const { data: wpArtists, isLoading, error } = useWPArtists();

  return {
    artists: wpArtists?.map(wpArtistToLocal) || [],
    isLoading,
    error,
  };
}

// Hook to get gallery pieces (replaces getGalleryPieces)
export function useGalleryPieces() {
  const { data: wpArtworks, isLoading, error } = useWPArtworks();

  return {
    pieces: wpArtworks?.filter(a => a.status === 'available').map(wpArtworkToLocal) || [],
    isLoading,
    error,
  };
}

// Hook to get depot pieces (replaces getDepotPieces)
export function useDepotPieces() {
  const { data: wpArtworks, isLoading, error } = useWPArtworks();

  return {
    pieces: wpArtworks?.filter(a => a.status !== 'available').map(wpArtworkToLocal) || [],
    isLoading,
    error,
  };
}

// Hook to get all galleries
export function useGalleriesData() {
  const { data: wpGalleries, isLoading, error } = useWPGalleries();

  return {
    galleries: wpGalleries?.map(wpGalleryToLocal) || [],
    isLoading,
    error,
  };
}

// Helper to get artist by ID
export function useArtistById(id: string) {
  const { artists, isLoading, error } = useArtists();

  return {
    artist: artists.find(a => a.id === id),
    isLoading,
    error,
  };
}

// Helper to get artwork by ID
export function useArtworkById(id: string) {
  const { data: wpArtworks, isLoading, error } = useWPArtworks();

  return {
    artwork: wpArtworks?.find(a => a.id.toString() === id),
    isLoading,
    error,
  };
}

// Format price (keep existing function)
export function formatPrice(price: number): string {
  return new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price);
}
```

### Phase 5: Update App.tsx (5 min)

Wrap app with QueryClientProvider in `src/App.tsx`:

The QueryClientProvider is already there! ✅ No changes needed.

### Phase 6: Migrate Individual Pages (20 min)

For each page that uses static data, replace imports:

**Example: `src/pages/Galleries.tsx`**

**BEFORE:**
```typescript
import { getGalleryPieces } from '@/data/collections';

const Galleries = () => {
  const galleryPieces = getGalleryPieces();
  // ... render
}
```

**AFTER:**
```typescript
import { useGalleryPieces } from '@/data/wordpress-adapter';
import LoadingSpinner from '@/components/LoadingScreen';

const Galleries = () => {
  const { pieces: galleryPieces, isLoading, error } = useGalleryPieces();

  if (isLoading) return <LoadingSpinner />;
  if (error) return <div>Error loading galleries</div>;

  // ... same render code
}
```

**Pages to update:**
- `src/pages/Index.tsx`
- `src/pages/Galleries.tsx`
- `src/pages/GalleryDetail.tsx`
- `src/pages/PieceDetail.tsx`
- `src/pages/Artists.tsx`
- `src/pages/Depot.tsx`
- Any other page using `getGalleryPieces()` or `getDepotPieces()`

### Phase 7: Test Development Mode (10 min)

```bash
# 1. Start Vite dev server
cd E:/projects/opus-gallery
npm run dev

# 2. Visit WordPress site
# Open: http://localhost/gallery-template/

# 3. Check browser console
# - No errors?
# - API calls working?
# - Data loading?

# 4. Test navigation
# - Click through pages
# - Check if content loads
```

### Phase 8: Build for Production (5 min)

```bash
cd E:/projects/opus-gallery

# Build React app
npm run build

# Check output
ls -la ../gallery-template/theme/dist/

# Should see:
# - dist/assets/index-[hash].js
# - dist/assets/index-[hash].css
# - dist/.vite/manifest.json
```

### Phase 9: Activate Theme & Test (10 min)

1. **Activate theme:**
   - Go to http://localhost/gallery-template/wp-admin
   - Appearance → Themes → Activate "OPUS Gallery"

2. **Visit site:**
   - Open http://localhost/gallery-template/
   - Should load React app

3. **Test pages:**
   - Home page
   - Galleries
   - Individual artworks
   - Artists

## Detailed File Changes

### File 1: `src/lib/wordpress-api.ts` (NEW)
Copy complete code from Phase 2 above.

### File 2: `src/data/wordpress-adapter.ts` (NEW)
Copy complete code from Phase 4 above.

### File 3: `vite.config.ts` (UPDATE)
Copy complete code from Phase 3 above.

### Files to Update: All pages using static data

Search for these imports:
```typescript
import { getGalleryPieces, getDepotPieces, artists } from '@/data/collections';
```

Replace with:
```typescript
import { useGalleryPieces, useDepotPieces, useArtists } from '@/data/wordpress-adapter';
```

Update component code from:
```typescript
const pieces = getGalleryPieces();
```

To:
```typescript
const { pieces, isLoading, error } = useGalleryPieces();

if (isLoading) return <LoadingSpinner />;
if (error) return <div>Error loading content</div>;
```

## WordPress Content Setup

Before testing, create content in WordPress:

### 1. Create Artists (8 artists)

For each artist:
1. Artists → Add New
2. Title: Artist name (e.g., "Joost de Jonge")
3. Content: Biography
4. Featured Image: Upload artist photo from `src/assets/artists/`
5. Custom Fields:
   - nationality: "Dutch"
   - birth_year: "1985"
6. Publish
7. **Note the ID** (you'll need it for artworks)

### 2. Create Galleries

1. Galleries → Add New
2. Title: "Archeology Series"
3. Content: Description
4. Featured Image: Upload
5. Publish
6. **Note the ID**

Repeat for:
- Flux Series
- Origin Series
- Ekphrasis Series
- Geometric Series
- Gestural Series

### 3. Create Artworks (~50 pieces)

For each artwork:
1. Artworks → Add New
2. Title: "Archeology I"
3. Content: Description
4. Featured Image: Upload from `src/assets/`
5. Custom Fields:
   - artist_id: (paste artist ID)
   - gallery_id: (paste gallery ID)
   - price: "2500"
   - year: "2023"
   - medium: "Oil on canvas"
   - dimensions: "120x80cm"
   - status: "available"
6. Publish

## Common Issues & Solutions

### Issue: CORS errors in dev mode

**Solution:** The theme already includes CORS headers. Verify `WP_DEBUG` is true:

Edit `E:\websites\gallery-template\wp-config.php`:
```php
define('WP_DEBUG', true);
```

### Issue: API returns empty array

**Solution:** Check that content is published (not draft).

### Issue: Images not loading

**Solution:**
1. Upload images to WordPress Media Library
2. Assign as Featured Image to posts
3. Check image URLs in API response

### Issue: Build not showing

**Solution:**
1. Check `dist/.vite/manifest.json` exists
2. Hard refresh browser (Ctrl+Shift+R)
3. Clear WordPress cache

## Quick Test Checklist

✅ Dependencies installed (`@tanstack/react-query`, `axios`)
✅ `wordpress-api.ts` created
✅ `wordpress-adapter.ts` created
✅ `vite.config.ts` updated
✅ Pages updated to use hooks
✅ WordPress content created (artists, galleries, artworks)
✅ Dev server works (`npm run dev`)
✅ Build works (`npm run build`)
✅ Theme activated in WordPress
✅ Site loads at http://localhost/gallery-template/
✅ API calls work (check Network tab)
✅ Content displays correctly
✅ Navigation works
✅ Images load

## Next Steps After Migration

1. **Migrate all static images to WordPress Media Library**
2. **Create all content (artists, galleries, artworks)**
3. **Test all pages thoroughly**
4. **Optimize queries (add caching if needed)**
5. **Deploy to production**

## Estimated Time

- **Setup:** 10 min
- **Code changes:** 30 min
- **WordPress content:** 30 min
- **Testing:** 20 min
- **Total:** ~90 minutes

Ready? Start with Phase 1!
