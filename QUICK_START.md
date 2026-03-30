# Quick Start Guide

Get your Lovable app running as a WordPress theme in minutes.

## What You Have

✅ **WordPress site:** `E:\websites\gallery-template`
✅ **React project:** `E:\projects\opus-gallery` (Lovable build)
✅ **Theme development:** `E:\projects\gallery-template\theme`
✅ **Junction:** WordPress themes → development theme

## Quick Start (5 Minutes)

### 1. Activate Theme in WordPress

```bash
# 1. Open WordPress admin
http://localhost/gallery-template/wp-admin

# 2. Go to Appearance → Themes
# 3. Find "OPUS Gallery" theme
# 4. Click "Activate"
```

### 2. Create Test Content

```bash
# In WordPress admin:

# Create an Artist:
Artists → Add New
- Title: "John Doe"
- Content: "Contemporary artist from Amsterdam"
- Featured Image: (upload photo)
- Publish

# Create a Gallery:
Galleries → Add New
- Title: "Spring Collection"
- Content: "A beautiful collection of spring artworks"
- Featured Image: (upload image)
- Publish

# Create an Artwork:
Artworks → Add New
- Title: "Sunset Dreams"
- Content: "Oil painting of a sunset"
- Featured Image: (upload image)
- Custom Fields:
  - artist_id: 1 (copy from Artists list)
  - gallery_id: 1 (copy from Galleries list)
  - price: 2500
  - year: 2024
  - medium: Oil on canvas
  - dimensions: 120x80cm
  - status: available
- Publish
```

### 3. Test REST API

```bash
# Open in browser:
http://localhost/gallery-template/wp-json/opus/v1/galleries
http://localhost/gallery-template/wp-json/opus/v1/artworks
http://localhost/gallery-template/wp-json/opus/v1/artists

# You should see JSON responses with your content
```

### 4. Prepare React App

```bash
cd E:/projects/opus-gallery

# Install dependencies
npm install @tanstack/react-query axios

# Create WordPress API wrapper
# (copy from BUILD_GUIDE.md Step 1.2)
# Save as: src/lib/wordpress-api.ts
```

### 5. Update Vite Config

```bash
# Edit vite.config.ts
# (copy from BUILD_GUIDE.md Step 1.3)
# This makes build output go to theme/dist/
```

### 6. Build React App

```bash
cd E:/projects/opus-gallery

# Build for production
npm run build

# This creates files in:
# E:/projects/gallery-template/theme/dist/
```

### 7. View Your Site

```bash
# Open WordPress site:
http://localhost/gallery-template/

# You should see:
# ✅ React app loads
# ✅ Beautiful UI (from Lovable)
# ✅ Content from WordPress
```

## Development Mode

For hot reload during development:

```bash
# 1. Start Vite dev server
cd E:/projects/opus-gallery
npm run dev

# 2. Visit WordPress site
http://localhost/gallery-template/

# The theme auto-detects dev server is running
# and loads from http://localhost:5173
# → Hot reload works!
```

## Architecture

```
┌─────────────────────────────────────────┐
│  LOVABLE PROJECT (opus-gallery)        │
│  Build beautiful UI with Vite + React   │
└──────────────┬──────────────────────────┘
               │
               ├─ npm run dev (development)
               │  → http://localhost:5173
               │
               └─ npm run build (production)
                  → theme/dist/
                     │
┌────────────────────▼─────────────────────┐
│  WORDPRESS THEME (opus-gallery)         │
│  E:\projects\gallery-template\theme\    │
│  ├─ index.php (loads React SPA)         │
│  ├─ functions.php (REST API)            │
│  └─ dist/ (React build)                 │
└──────────────┬──────────────────────────┘
               │ (junction)
┌──────────────▼──────────────────────────┐
│  WORDPRESS SITE                          │
│  E:\websites\gallery-template\          │
│  └─ wp-content/themes/opus-gallery/     │
└──────────────────────────────────────────┘
```

## File Structure

```
E:\projects\
├── opus-gallery/          # Lovable React project
│   ├── src/
│   │   ├── pages/        # React pages
│   │   ├── components/   # React components
│   │   └── lib/
│   │       └── wordpress-api.ts  # ← CREATE THIS
│   ├── vite.config.ts    # ← UPDATE THIS
│   └── package.json
│
└── gallery-template/      # Theme development
    ├── theme/            # WordPress theme
    │   ├── style.css
    │   ├── index.php
    │   ├── functions.php
    │   └── dist/         # ← BUILD OUTPUT GOES HERE
    ├── ARCHITECTURE.md   # System overview
    ├── BUILD_GUIDE.md    # Complete guide
    └── QUICK_START.md    # This file

E:\websites\
└── gallery-template/      # WordPress installation
    └── wp-content/
        └── themes/
            └── opus-gallery/  # → Junction to theme/
```

## Next Steps

1. ✅ Read this Quick Start
2. ⏭️ Read `BUILD_GUIDE.md` Step 1.2 (Create API wrapper)
3. ⏭️ Read `BUILD_GUIDE.md` Step 1.3 (Update Vite config)
4. ⏭️ Read `BUILD_GUIDE.md` Step 2 (Migrate components)
5. ⏭️ Build & deploy!

## Common Commands

```bash
# Develop with hot reload
cd E:/projects/opus-gallery && npm run dev

# Build for production
cd E:/projects/opus-gallery && npm run build

# View site
http://localhost/gallery-template/

# View WordPress admin
http://localhost/gallery-template/wp-admin

# Test REST API
http://localhost/gallery-template/wp-json/opus/v1/galleries
```

## Key Concepts

### 1. **Two Environments**
- **Development**: React on Vite dev server (http://localhost:5173) with hot reload
- **Production**: React built to `dist/`, served by WordPress

### 2. **Automatic Detection**
The theme checks if `dist/.vite/manifest.json` exists:
- ✅ **Exists** → Production mode (load from dist/)
- ❌ **Missing** → Development mode (load from Vite dev server)

### 3. **Data Flow**
```
WordPress Admin → Create Content
     ↓
REST API → /wp-json/opus/v1/galleries
     ↓
React Hooks → useGalleries()
     ↓
React Components → Display beautifully
```

### 4. **No PHP Required**
React developers never write PHP. All logic in TypeScript.
WordPress provides: content API, admin UI, image hosting.

## Troubleshooting

**Theme not showing in WordPress?**
- Check junction exists: `ls E:/websites/gallery-template/wp-content/themes/`
- Should see `opus-gallery` directory

**Build not working?**
- Check Vite config points to correct output directory
- Run `npm run build` and verify `theme/dist/` has files

**React app not loading?**
- Check browser console for errors
- Verify `window.wpData` exists (View Source → see `<script>` tag)
- Check REST API endpoints return data

**Hot reload not working?**
- Ensure `npm run dev` is running
- Visit http://localhost:5173 directly to verify Vite works
- Check WordPress functions.php has CORS headers for localhost

## Support

See `BUILD_GUIDE.md` for complete documentation.
