# OPUS Gallery WordPress Theme

A modern WordPress theme built with React. Features cinematic animations, parallax effects, and headless WordPress architecture.

## Architecture

This theme bridges **React development** (Lovable/Vite) with **WordPress content management**:

```
React SPA (Frontend)  ←→  WordPress REST API (Backend)
     ↓                          ↓
  Beautiful UI            Content Management
  Animations              Admin Interface
  Fast Routing            SEO & Permalinks
```

## Directory Structure

```
theme/
├── style.css              # WordPress theme metadata
├── index.php              # SPA loader (loads React app)
├── functions.php          # REST API, custom post types
├── screenshot.png         # Theme preview (1200x900)
├── dist/                  # Built React app (from npm run build)
│   ├── index.html
│   ├── assets/
│   │   ├── index-[hash].js
│   │   └── index-[hash].css
│   └── .vite/
│       └── manifest.json
└── README.md             # This file
```

## Development Workflow

### 1. Setup WordPress

```bash
# The theme is symlinked from E:\projects\gallery-template\theme
# to E:\websites\gallery-template\wp-content\themes\opus-gallery

# Activate theme in WordPress admin
```

### 2. Develop React App

```bash
# Work in your Lovable project (opus-gallery)
cd E:/projects/opus-gallery

# Run Vite dev server
npm run dev
# → Opens http://localhost:5173

# The WordPress theme will load from dev server
# (see functions.php dev mode detection)
```

### 3. Build for Production

```bash
# Build React app
cd E:/projects/opus-gallery
npm run build
# → Creates dist/ folder

# Copy dist/ to theme
cp -r dist/* E:/projects/gallery-template/theme/dist/

# WordPress will now load the built version
```

## How It Works

### React Development (Lovable)

1. Build UI in Lovable with mock data
2. Components use standard React patterns
3. Fast hot reload during development

### WordPress Integration

1. Replace mock data imports with WordPress API hooks
2. Build React app (`npm run build`)
3. Copy `dist/` to theme directory
4. WordPress serves the React SPA

### Example Data Migration

**Before (Lovable with mock data):**
```typescript
// src/pages/Galleries.tsx
import { getGalleryPieces } from '@/data/collections';

const Galleries = () => {
  const galleries = getGalleryPieces(); // Static mock data
  return <div>{/* render galleries */}</div>;
}
```

**After (WordPress integration):**
```typescript
// src/pages/Galleries.tsx
import { useGalleries } from '@/lib/wordpress-api';

const Galleries = () => {
  const { data: galleries, isLoading } = useGalleries(); // WordPress REST API
  if (isLoading) return <LoadingSpinner />;
  return <div>{/* same render code */}</div>;
}
```

The UI code **doesn't change**, only the data source!

## Custom Post Types

This theme registers 4 custom post types:

### 1. Galleries (`opus_gallery`)
- Title: Gallery name
- Content: Gallery description
- Featured Image: Gallery hero image
- Meta: Custom fields

### 2. Artworks (`opus_artwork`)
- Title: Artwork title
- Content: Artwork description
- Featured Image: Artwork image
- Meta:
  - `artist_id`: Artist ID (FK)
  - `gallery_id`: Gallery ID (FK)
  - `price`: Price in EUR
  - `year`: Creation year
  - `medium`: Medium (oil, watercolor, etc.)
  - `dimensions`: Dimensions (e.g., "120x80cm")
  - `status`: available|sold|on-loan

### 3. Artists (`opus_artist`)
- Title: Artist name
- Content: Artist biography
- Featured Image: Artist photo
- Meta:
  - `nationality`: Nationality
  - `birth_year`: Birth year
  - `website`: Artist website URL

### 4. Exhibitions (`opus_exhibition`)
- Title: Exhibition name
- Content: Exhibition description
- Featured Image: Exhibition image
- Meta:
  - `start_date`: Start date (YYYY-MM-DD)
  - `end_date`: End date (YYYY-MM-DD)
  - `location`: Location
  - `status`: upcoming|current|past

## REST API Endpoints

All endpoints return JSON:

```
GET /wp-json/opus/v1/galleries
GET /wp-json/opus/v1/galleries/{id}
GET /wp-json/opus/v1/artworks
GET /wp-json/opus/v1/artworks/{id}
GET /wp-json/opus/v1/artworks?gallery_id={id}
GET /wp-json/opus/v1/artists
GET /wp-json/opus/v1/exhibitions
```

Example response:
```json
{
  "id": 123,
  "title": "Contemporary Collection",
  "slug": "contemporary-collection",
  "description": "<p>A curated collection...</p>",
  "featured_image": "https://example.com/wp-content/uploads/2024/03/hero.jpg",
  "thumbnail": "https://example.com/wp-content/uploads/2024/03/hero-400x400.jpg",
  "date": "2024-03-15T10:30:00+00:00"
}
```

## WordPress Data Available to React

The theme passes WordPress data to React via `window.wpData`:

```javascript
window.wpData = {
  apiUrl: 'https://yoursite.com/wp-json/opus/v1',
  siteUrl: 'https://yoursite.com',
  nonce: 'abc123...', // For authenticated requests
  siteName: 'OPUS Gallery',
  siteDescription: 'Contemporary Art Gallery',
  language: 'en-US'
}
```

Access in React:
```typescript
const API_URL = (window as any).wpData?.apiUrl || '/wp-json/opus/v1';
```

## Development vs Production

The theme auto-detects environment:

**Development Mode** (`npm run dev` active):
- Loads from Vite dev server (`http://localhost:5173`)
- Hot reload enabled
- CORS headers added for localhost

**Production Mode** (built files present):
- Loads from `dist/` folder
- Optimized bundles with cache busting
- Production performance

Detection: checks for `dist/.vite/manifest.json`

## Image Sizes

Custom image sizes registered:

- `opus-thumbnail`: 400x400 (cropped) - Gallery grid thumbnails
- `opus-medium`: 800x600 (cropped) - Artwork detail previews
- `opus-large`: 1600x1200 (uncropped) - Lightbox display
- `opus-hero`: 2400x1600 (uncropped) - Hero sections

Access in REST API:
```php
get_the_post_thumbnail_url($post, 'opus-large');
```

## Security

- Theme file editor disabled (`DISALLOW_FILE_EDIT`)
- REST API authentication via nonce
- CORS restricted to localhost in dev mode
- Direct PHP file access prevented

## Next Steps

1. **Build React Data Adapter**
   - Create `wordpress-api.ts` wrapper
   - Build React hooks (`useGalleries`, `useArtworks`, etc.)
   - Handle loading states, errors, caching

2. **Create Build Script**
   - Automate `npm run build` → copy to theme
   - Update manifest paths
   - Clear WordPress cache

3. **Migrate opus-gallery**
   - Replace mock data with WordPress hooks
   - Test all pages with real API
   - Deploy to production

## Support

- **WordPress**: 6.0+
- **PHP**: 8.0+
- **Node**: 18+
- **React**: 18+

## License

GPL v2 or later
