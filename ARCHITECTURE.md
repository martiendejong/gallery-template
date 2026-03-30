# WordPress React Bridge - Architecture

## Overview

This system allows you to build beautiful React apps in Lovable (or similar) and deploy them as WordPress themes while maintaining WordPress content management.

## The Problem

1. **Lovable** makes it easy to build beautiful React UIs quickly
2. **WordPress** is great for content management but themes are hard to build
3. **Clients** want WordPress admin but modern UI
4. **Developers** want to work with React, not PHP templates

## The Solution

A 3-layer architecture that bridges React and WordPress:

```
┌──────────────────────────────────────────────────────┐
│ Layer 1: LOVABLE APP (Development)                  │
│ • Build UI rapidly with Lovable                     │
│ • Use mock data during development                  │
│ • Full React ecosystem (Router, Framer Motion, etc) │
└──────────────────────────────────────────────────────┘
                     │
                     ▼ npm run build
┌──────────────────────────────────────────────────────┐
│ Layer 2: REACT APP (Production Bundle)              │
│ • Static HTML/CSS/JS files in dist/                 │
│ • All routes handled by React Router                │
│ • API calls to WordPress REST endpoints             │
└──────────────────────────────────────────────────────┘
                     │
                     ▼ wp-theme-wrapper
┌──────────────────────────────────────────────────────┐
│ Layer 3: WORDPRESS THEME                             │
│ • index.php loads React SPA                         │
│ • functions.php registers API endpoints             │
│ • Custom post types for content                     │
│ • Admin UI for content management                   │
└──────────────────────────────────────────────────────┘
```

## Directory Structure

```
gallery-template/
├── plugin/                    # WordPress plugin (content API)
│   └── wp-react-bridge/
│       ├── wp-react-bridge.php       # Main plugin file
│       ├── includes/
│       │   ├── post-types.php        # Register CPTs
│       │   ├── api-endpoints.php     # REST API
│       │   └── admin-ui.php          # Admin pages
│       └── assets/                   # Admin CSS/JS
│
├── theme-generator/           # Converts React build → WP theme
│   ├── generate-theme.js            # Main script
│   ├── templates/
│   │   ├── index.php.template       # SPA loader
│   │   ├── functions.php.template   # Theme functions
│   │   └── style.css.template       # Theme metadata
│   └── config.json                  # Conversion settings
│
└── data-adapter/              # WordPress API client for React
    ├── wordpress-api.ts             # API wrapper
    ├── hooks/
    │   ├── useGalleries.ts          # Gallery hooks
    │   ├── useArtworks.ts           # Artwork hooks
    │   └── useArtists.ts            # Artist hooks
    └── types/
        └── wordpress.ts             # TypeScript types
```

## Content Flow

### 1. WordPress Admin (Content Creation)
```
Admin creates:
├── Galleries (Custom Post Type)
├── Artworks (Custom Post Type)
├── Artists (Custom Post Type)
├── Exhibitions (Custom Post Type)
└── Media Library (Images)
```

### 2. WordPress REST API (Data Source)
```
Available endpoints:
├── /wp-json/opus/v1/galleries
├── /wp-json/opus/v1/artworks
├── /wp-json/opus/v1/artists
├── /wp-json/opus/v1/exhibitions
└── /wp-json/wp/v2/media
```

### 3. React App (Display)
```
React components fetch data:
├── useGalleries() → GET /wp-json/opus/v1/galleries
├── useArtworks() → GET /wp-json/opus/v1/artworks
└── Display with beautiful UI (Lovable design)
```

## Development Workflow

### Initial Setup (One Time)
```bash
# 1. Install WordPress plugin
wp plugin install ./plugin/wp-react-bridge --activate

# 2. Create content in WordPress admin
# (galleries, artworks, artists)

# 3. Configure React app to use WordPress API
npm install
# Update src/lib/api.ts with WordPress URL
```

### Build & Deploy Workflow
```bash
# 1. Build React app
cd opus-gallery
npm run build
# → Creates dist/ folder

# 2. Convert to WordPress theme
cd ../gallery-template
node theme-generator/generate-theme.js ../opus-gallery/dist
# → Creates wp-themes/opus-gallery/

# 3. Deploy to WordPress
# Copy wp-themes/opus-gallery/ to WordPress wp-content/themes/
# Activate theme in WordPress admin
```

### Development Cycle
```bash
# Option A: Develop with mock data
cd opus-gallery
npm run dev
# → Edit components with static data

# Option B: Develop with live WordPress
npm run dev
# Configure API to point to local WordPress
# → Edit components with real data
```

## Key Features

### 1. Zero PHP Required
- React developers never touch PHP
- All logic in TypeScript
- WordPress just provides REST API

### 2. Beautiful UI, Easy Content
- Clients get modern, animated UI
- Clients edit content in familiar WordPress admin
- No custom theme builder needed

### 3. Fast Development
- Build UI in Lovable (hours, not days)
- One-command conversion to WordPress theme
- Reuse across projects

### 4. Performance
- React SPA = fast client-side routing
- WordPress REST API = cacheable
- Static assets served by CDN

### 5. Maintainable
- Clear separation: UI (React) vs Content (WordPress)
- Standard WordPress for clients
- Standard React for developers

## Example: Gallery Page

### Before (Lovable development)
```typescript
// src/pages/Galleries.tsx
import { getGalleryPieces } from '@/data/collections';

const Galleries = () => {
  const galleries = getGalleryPieces(); // Static data
  // ... render galleries
}
```

### After (WordPress integration)
```typescript
// src/pages/Galleries.tsx
import { useGalleries } from '@/lib/wordpress-api';

const Galleries = () => {
  const { data: galleries } = useGalleries(); // WordPress API
  // ... same render code (no changes!)
}
```

The UI code **doesn't change**. Only the data source changes.

## Technical Details

### WordPress Theme Structure
```
opus-gallery-theme/
├── index.php              # Loads React SPA
├── functions.php          # Theme setup, REST API
├── style.css              # Theme metadata
├── assets/
│   ├── css/              # Vite build output
│   ├── js/               # React bundle
│   └── images/           # Static images
└── screenshot.png        # Theme preview
```

### index.php (SPA Loader)
```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <?php wp_head(); ?>
</head>
<body>
    <div id="root"></div>
    <?php wp_footer(); ?>
</body>
</html>
```

### functions.php (API Setup)
```php
<?php
// Enqueue React app
function opus_enqueue_scripts() {
    // Vite build outputs with hashed filenames
    $manifest = json_decode(file_get_contents(get_template_directory() . '/assets/manifest.json'), true);

    wp_enqueue_script('opus-app', get_template_directory_uri() . '/assets/' . $manifest['main.js'], [], null, true);
    wp_enqueue_style('opus-style', get_template_directory_uri() . '/assets/' . $manifest['main.css']);

    // Pass WordPress data to React
    wp_localize_script('opus-app', 'wpData', [
        'apiUrl' => rest_url('opus/v1'),
        'nonce' => wp_create_nonce('wp_rest')
    ]);
}
add_action('wp_enqueue_scripts', 'opus_enqueue_scripts');
```

## Next Steps

1. **Build the plugin** - WordPress content types & REST API
2. **Build the adapter** - React hooks for WordPress data
3. **Build the generator** - Automated theme conversion
4. **Test with opus-gallery** - Full integration test
5. **Document** - Usage guide for future projects

## Benefits Summary

✅ **For Developers:**
- Work in React, not PHP
- Use Lovable for rapid UI development
- Modern tooling (Vite, TypeScript, Tailwind)

✅ **For Clients:**
- Familiar WordPress admin
- Beautiful, modern UI
- Easy content management

✅ **For Business:**
- Fast project delivery (UI in hours)
- Reusable system across projects
- Lower maintenance costs

This is **headless WordPress done right** - the UI layer is completely separate but still delivered as a standard WordPress theme.
