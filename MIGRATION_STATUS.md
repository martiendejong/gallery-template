# Migration Status - OPUS Gallery

**Date:** 2026-03-30
**Status:** ✅ Phase 1-3 Complete, Ready for Content & Page Migration

---

## ✅ Completed

### 1. WordPress Theme Structure
- ✅ Theme files created in `E:\projects\gallery-template\theme\`
- ✅ Junction/symlink created to `E:\websites\gallery-template\wp-content\themes\opus-gallery`
- ✅ Theme includes:
  - `style.css` - WordPress theme metadata
  - `index.php` - SPA loader (passes `window.wpData` to React)
  - `functions.php` - Custom post types + REST API endpoints
  - `README.md` - Theme documentation

### 2. React App Configuration
- ✅ Dependencies installed:
  - `@tanstack/react-query` - Data fetching
  - `axios` - HTTP client
- ✅ Created `src/lib/wordpress-api.ts`:
  - API client with `getApiUrl()` detection
  - TypeScript interfaces for all WordPress types
  - React Query hooks: `useWPGalleries()`, `useWPArtworks()`, `useWPArtists()`, etc.
  - Adapter functions: `wpArtistToLocal()`, `wpArtworkToLocal()`, `wpGalleryToLocal()`
- ✅ Created `src/data/wordpress-adapter.ts`:
  - Wrapper hooks maintaining existing interface
  - `useArtists()`, `useGalleryPieces()`, `useDepotPieces()`, etc.
- ✅ Updated `vite.config.ts`:
  - Build output: `../gallery-template/theme/dist`
  - Manifest generation enabled
  - CORS enabled for development
- ✅ Build completed successfully:
  - 2,207 modules transformed
  - All assets in `theme/dist/`
  - `.vite/manifest.json` generated
  - 70+ images, CSS (84 KB), JS (900 KB)

### 3. XAMPP Configuration
- ✅ Fixed `httpd-vhosts.conf` to point `localhost` to `gallery-template`
- ✅ Apache restart required to take effect

---

## 🚧 Next Steps

### Phase 4: Activate WordPress Theme
1. Open http://localhost/gallery-template/wp-admin
2. Go to Appearance → Themes
3. Activate "OPUS Gallery" theme
4. Visit http://localhost/gallery-template/ to see React app load

### Phase 5: Create WordPress Content

#### A. Create Artists (8 artists)
For each artist (Floyd, John, Joost, Loes, Milan, Peter R, Peter C, Raymon):
1. Artists → Add New
2. Title: Artist name
3. Content: Biography
4. Featured Image: Upload from `E:\projects\opus-gallery\src\assets\artists\`
5. Custom Fields:
   - `nationality`: "Dutch"
   - `birth_year`: "1985"
   - `website`: "https://example.com"
6. Publish
7. **Note the Artist ID** (needed for artworks)

#### B. Create Galleries (6 galleries)
1. Galleries → Add New
2. Title: "Archeology Series", "Flux Series", etc.
3. Content: Description
4. Featured Image: Upload gallery image
5. Publish
6. **Note the Gallery ID**

#### C. Create Artworks (~50 pieces)
1. Artworks → Add New
2. Title: Artwork title
3. Content: Description
4. Featured Image: Upload from `E:\projects\opus-gallery\src\assets\`
5. Custom Fields:
   - `artist_id`: (paste artist ID from step A)
   - `gallery_id`: (paste gallery ID from step B)
   - `price`: "2500"
   - `year`: "2023"
   - `medium`: "Oil on canvas"
   - `dimensions`: "120x80cm"
   - `status`: "available"
6. Publish

### Phase 6: Migrate React Pages (20-30 min)

**Pages to update:** (See MIGRATION_PLAN.md for examples)
- `src/pages/Index.tsx`
- `src/pages/Galleries.tsx`
- `src/pages/GalleryDetail.tsx`
- `src/pages/PieceDetail.tsx`
- `src/pages/Artists.tsx`
- `src/pages/Depot.tsx`

**Pattern for each page:**

**Before:**
```typescript
import { getGalleryPieces } from '@/data/collections';
const galleries = getGalleryPieces();
```

**After:**
```typescript
import { useGalleryPieces } from '@/data/wordpress-adapter';
import LoadingSpinner from '@/components/LoadingScreen';

const { pieces: galleries, isLoading, error } = useGalleryPieces();
if (isLoading) return <LoadingSpinner />;
if (error) return <div>Error loading galleries</div>;
```

### Phase 7: Test Development Mode (5 min)
```bash
cd E:/projects/opus-gallery
npm run dev
```
Visit http://localhost/gallery-template/ - should load from Vite dev server with hot reload

### Phase 8: Rebuild for Production (2 min)
```bash
cd E:/projects/opus-gallery
npm run build
```
Hard refresh browser (Ctrl+Shift+R) to see production build

---

## Architecture Overview

```
┌─────────────────────────────────────┐
│  React App (opus-gallery)           │
│  - Builds to theme/dist/            │
│  - Fetches from WordPress REST API  │
│  - Beautiful UI with animations     │
└──────────────┬──────────────────────┘
               │
               ├─ Development: http://localhost:5173 (Vite dev server)
               └─ Production: theme/dist/ (built files)
                     │
┌────────────────────▼─────────────────┐
│  WordPress Theme (opus-gallery)      │
│  - index.php loads React SPA         │
│  - functions.php provides REST API   │
│  - Auto-detects dev vs production    │
└──────────────┬──────────────────────┘
               │ (junction/symlink)
┌──────────────▼──────────────────────┐
│  WordPress Site (localhost)          │
│  E:\websites\gallery-template\       │
│  - Content management in admin       │
│  - REST API at /wp-json/opus/v1/*   │
└──────────────────────────────────────┘
```

## REST API Endpoints

Available at `http://localhost/gallery-template/wp-json/opus/v1/`:
- `/galleries` - List all galleries
- `/galleries/{id}` - Get single gallery
- `/artworks` - List all artworks
- `/artworks/{id}` - Get single artwork
- `/artworks?gallery_id={id}` - Filter by gallery
- `/artists` - List all artists
- `/exhibitions` - List all exhibitions

---

## Development Workflow

### Daily Development:
1. Start Vite dev server: `cd E:/projects/opus-gallery && npm run dev`
2. Visit http://localhost/gallery-template/ (loads from Vite with hot reload)
3. Edit React components in `opus-gallery/src/`
4. Changes appear instantly

### Deploy to Production:
1. Build: `cd E:/projects/opus-gallery && npm run build`
2. Hard refresh browser: Ctrl+Shift+R
3. WordPress serves built files from `theme/dist/`

---

## Troubleshooting

### React app not loading?
- Check browser console for errors
- Verify `window.wpData` exists (View Source → `<script>` tag)
- Test REST API: http://localhost/gallery-template/wp-json/opus/v1/galleries

### Images not loading?
- Upload images to WordPress Media Library
- Set as Featured Image on posts
- Check image URLs in API response

### Hot reload not working?
- Ensure `npm run dev` is running
- Visit http://localhost:5173 directly to verify Vite works
- Check CORS headers in functions.php

### Build not showing?
- Check `theme/dist/.vite/manifest.json` exists
- Hard refresh: Ctrl+Shift+R
- Clear WordPress cache

---

## Files Created/Modified

### Created:
- `E:\projects\opus-gallery\src\lib\wordpress-api.ts`
- `E:\projects\opus-gallery\src\data\wordpress-adapter.ts`
- `E:\projects\gallery-template\theme\style.css`
- `E:\projects\gallery-template\theme\index.php`
- `E:\projects\gallery-template\theme\functions.php`
- `E:\projects\gallery-template\theme\README.md`
- `E:\projects\gallery-template\theme\.gitignore`
- `E:\projects\gallery-template\ARCHITECTURE.md`
- `E:\projects\gallery-template\BUILD_GUIDE.md`
- `E:\projects\gallery-template\QUICK_START.md`
- `E:\projects\gallery-template\MIGRATION_PLAN.md`
- `E:\projects\gallery-template\MIGRATION_STATUS.md` (this file)

### Modified:
- `E:\projects\opus-gallery\vite.config.ts` - Added build output configuration
- `E:\xampp\apache\conf\extra\httpd-vhosts.conf` - Fixed localhost DocumentRoot

---

## Estimated Remaining Time

- **Phase 4** (Activate theme): 2 minutes
- **Phase 5** (Create content): 30 minutes
- **Phase 6** (Migrate pages): 20 minutes
- **Phase 7** (Test): 5 minutes
- **Phase 8** (Build): 2 minutes

**Total:** ~60 minutes

---

## Next Command

```bash
# Restart Apache (via XAMPP Control Panel)
# Then activate the theme in WordPress admin
```

Ready to continue with Phase 4!
