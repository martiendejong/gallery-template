# Build Guide: Lovable to WordPress Theme

Complete guide for converting a Lovable React app to a WordPress theme.

## Prerequisites

✅ WordPress site running (E:\websites\gallery-template)
✅ Lovable project built (E:\projects\opus-gallery)
✅ Theme directory created (E:\projects\gallery-template\theme)
✅ Symlink created (themes/opus-gallery → theme/)

## Step 1: Prepare React App

### 1.1 Install WordPress API Client

```bash
cd E:/projects/opus-gallery

# Install dependencies
npm install @tanstack/react-query axios
```

### 1.2 Create WordPress API Wrapper

Create `src/lib/wordpress-api.ts`:

```typescript
import axios from 'axios';
import { useQuery } from '@tanstack/react-query';

// Get WordPress API URL from window
const getApiUrl = () => {
  return (window as any).wpData?.apiUrl || '/wp-json/opus/v1';
};

// API client
const api = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
  },
});

// Types
export interface Gallery {
  id: number;
  title: string;
  slug: string;
  description: string;
  featured_image: string;
  thumbnail: string;
  date: string;
}

export interface Artwork {
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

export interface Artist {
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

export interface Exhibition {
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

// React Hooks
export function useGalleries() {
  return useQuery<Gallery[]>({
    queryKey: ['galleries'],
    queryFn: async () => {
      const { data } = await api.get('/galleries');
      return data;
    },
  });
}

export function useGallery(id: number | string) {
  return useQuery<Gallery>({
    queryKey: ['gallery', id],
    queryFn: async () => {
      const { data } = await api.get(`/galleries/${id}`);
      return data;
    },
    enabled: !!id,
  });
}

export function useArtworks(galleryId?: number) {
  return useQuery<Artwork[]>({
    queryKey: ['artworks', galleryId],
    queryFn: async () => {
      const params = galleryId ? `?gallery_id=${galleryId}` : '';
      const { data } = await api.get(`/artworks${params}`);
      return data;
    },
  });
}

export function useArtwork(id: number | string) {
  return useQuery<Artwork>({
    queryKey: ['artwork', id],
    queryFn: async () => {
      const { data } = await api.get(`/artworks/${id}`);
      return data;
    },
    enabled: !!id,
  });
}

export function useArtists() {
  return useQuery<Artist[]>({
    queryKey: ['artists'],
    queryFn: async () => {
      const { data } = await api.get('/artists');
      return data;
    },
  });
}

export function useExhibitions() {
  return useQuery<Exhibition[]>({
    queryKey: ['exhibitions'],
    queryFn: async () => {
      const { data } = await api.get('/exhibitions');
      return data;
    },
  });
}
```

### 1.3 Update Vite Config for WordPress

Edit `vite.config.ts`:

```typescript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react-swc';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    // Output to theme/dist/
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
  server: {
    // CORS for WordPress dev
    cors: true,
    port: 5173,
  },
});
```

## Step 2: Migrate Components

### 2.1 Replace Mock Data Imports

**Before:**
```typescript
// src/pages/Galleries.tsx
import { getGalleryPieces } from '@/data/collections';

const Galleries = () => {
  const galleries = getGalleryPieces();

  return (
    <div>
      {galleries.map(gallery => (
        <GalleryCard key={gallery.id} gallery={gallery} />
      ))}
    </div>
  );
};
```

**After:**
```typescript
// src/pages/Galleries.tsx
import { useGalleries } from '@/lib/wordpress-api';
import LoadingSpinner from '@/components/LoadingSpinner';

const Galleries = () => {
  const { data: galleries, isLoading, error } = useGalleries();

  if (isLoading) return <LoadingSpinner />;
  if (error) return <div>Error loading galleries</div>;
  if (!galleries) return null;

  return (
    <div>
      {galleries.map(gallery => (
        <GalleryCard key={gallery.id} gallery={gallery} />
      ))}
    </div>
  );
};
```

### 2.2 Update Types

Match component props to WordPress API types:

```typescript
// Before (mock data types)
interface Gallery {
  id: string;
  name: string;
  pieces: Artwork[];
}

// After (WordPress API types)
interface Gallery {
  id: number;
  title: string; // WordPress uses 'title' not 'name'
  slug: string;
  featured_image: string;
}
```

### 2.3 Handle Images

**Before (static imports):**
```typescript
import heroImage from '@/assets/hero.jpg';
<img src={heroImage} alt="Hero" />
```

**After (WordPress URLs):**
```typescript
<img src={gallery.featured_image} alt={gallery.title} />
```

## Step 3: Build & Deploy

### 3.1 Build React App

```bash
cd E:/projects/opus-gallery

# Build for production
npm run build

# This creates:
# ../gallery-template/theme/dist/
#   ├── index.html
#   ├── assets/
#   │   ├── index-[hash].js
#   │   └── index-[hash].css
#   └── .vite/
#       └── manifest.json
```

### 3.2 Verify Build

```bash
# Check files were created
ls -la E:/projects/gallery-template/theme/dist/

# Should see:
# - dist/index.html
# - dist/assets/*.js
# - dist/assets/*.css
# - dist/.vite/manifest.json
```

### 3.3 Activate Theme in WordPress

1. Go to WordPress admin: http://localhost/gallery-template/wp-admin
2. Navigate to Appearance → Themes
3. Find "OPUS Gallery" theme
4. Click "Activate"

## Step 4: Create Content in WordPress

### 4.1 Create Artists

1. In WordPress admin, go to Artists → Add New
2. Fill in:
   - Title: Artist name (e.g., "Peter Riezebos")
   - Content: Biography
   - Featured Image: Artist photo
   - Custom Fields:
     - `nationality`: "Dutch"
     - `birth_year`: "1978"
     - `website`: "https://example.com"
3. Publish

### 4.2 Create Galleries

1. Go to Galleries → Add New
2. Fill in:
   - Title: Gallery name (e.g., "Contemporary Collection")
   - Content: Gallery description
   - Featured Image: Gallery hero image
3. Publish

### 4.3 Create Artworks

1. Go to Artworks → Add New
2. Fill in:
   - Title: Artwork title
   - Content: Artwork description
   - Featured Image: Artwork image
   - Custom Fields:
     - `artist_id`: (copy artist ID from Artists list)
     - `gallery_id`: (copy gallery ID from Galleries list)
     - `price`: "2500"
     - `year`: "2023"
     - `medium`: "Oil on canvas"
     - `dimensions`: "120x80cm"
     - `status`: "available"
3. Publish

## Step 5: Test

### 5.1 Check REST API

Visit in browser:
- http://localhost/gallery-template/wp-json/opus/v1/galleries
- http://localhost/gallery-template/wp-json/opus/v1/artworks
- http://localhost/gallery-template/wp-json/opus/v1/artists

Should see JSON responses.

### 5.2 Test Frontend

Visit: http://localhost/gallery-template/

Should see:
✅ React app loads
✅ Galleries page shows WordPress content
✅ Navigation works
✅ Images load from WordPress media library

## Development Workflow

### Day-to-Day Development

```bash
# 1. Run Vite dev server
cd E:/projects/opus-gallery
npm run dev

# 2. Visit WordPress site
# http://localhost/gallery-template/

# The theme will load from Vite dev server (hot reload!)

# 3. Make changes to React components
# Changes appear instantly

# 4. When ready to deploy:
npm run build
# Refreshin WordPress
```

### Adding New Content Types

1. **Add CPT to functions.php**
```php
register_post_type('opus_collection', [
  'labels' => ['name' => 'Collections'],
  'public' => true,
  'show_in_rest' => true,
  'supports' => ['title', 'editor', 'thumbnail'],
]);
```

2. **Add REST endpoint**
```php
register_rest_route('opus/v1', '/collections', [
  'methods' => 'GET',
  'callback' => 'opus_get_collections',
]);
```

3. **Add React hook**
```typescript
export function useCollections() {
  return useQuery<Collection[]>({
    queryKey: ['collections'],
    queryFn: async () => {
      const { data } = await api.get('/collections');
      return data;
    },
  });
}
```

4. **Use in components**
```typescript
const { data: collections } = useCollections();
```

## Troubleshooting

### Build not showing in WordPress

1. Check manifest exists: `E:/projects/gallery-template/theme/dist/.vite/manifest.json`
2. Clear WordPress cache: WP Admin → Settings → Performance
3. Hard refresh browser: Ctrl+Shift+R

### CORS errors in dev mode

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
```

This enables CORS headers in `functions.php`.

### Images not loading

1. Check image URLs in REST API response
2. Verify images uploaded to WordPress Media Library
3. Check image sizes registered in `functions.php`

### 404 on React routes

WordPress handles all requests. If you visit `/galleries` directly:
1. WordPress loads `index.php`
2. React Router takes over
3. Renders Galleries component

This is **correct behavior**. WordPress serves the SPA for all routes.

## Production Deployment

### 1. Build

```bash
cd E:/projects/opus-gallery
npm run build
```

### 2. Upload Theme

Upload `E:/projects/gallery-template/theme/` to production server:

```
wp-content/themes/opus-gallery/
├── style.css
├── index.php
├── functions.php
├── dist/
│   └── (all built files)
└── README.md
```

### 3. Activate

In production WordPress admin:
1. Go to Appearance → Themes
2. Activate "OPUS Gallery"

### 4. Create Content

Same process as Step 4 above.

## Next Steps

1. ✅ Theme structure created
2. ✅ REST API endpoints working
3. ⏭️ Create WordPress API client (`wordpress-api.ts`)
4. ⏭️ Migrate opus-gallery components
5. ⏭️ Build & test
6. ⏭️ Deploy to production

Start with Step 1.2 (Create WordPress API Wrapper) next!
