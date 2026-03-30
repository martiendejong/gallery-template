# WordPress Menu Setup Guide

## What We Just Built

The React app now fetches its navigation menu directly from WordPress. Here's what's in place:

### Backend (WordPress)
- ✅ Registered menu location: `header-menu`
- ✅ REST API endpoint: `/wp-json/opus/v1/menu`
- ✅ Menu data formatting with slug, title, URL, and order

### Frontend (React)
- ✅ `useWPMenu()` hook to fetch menu from WordPress
- ✅ Header.tsx updated to use WordPress menu
- ✅ Automatic fallback to hardcoded menu if WordPress menu not set

## Step-by-Step Setup in WordPress

### Step 1: Create Pages in WordPress Admin

1. Go to http://localhost/gallery-template/wp-admin
2. Navigate to **Pages → Add New**
3. Create these 6 pages:

   **Home Page:**
   - Title: `Home`
   - Slug: `home`
   - Content: (Leave blank or add welcome text)
   - Status: Publish

   **Collection Page:**
   - Title: `Collection`
   - Slug: `galleries` (Important: must match the route!)
   - Content: (Leave blank - React will handle display)
   - Status: Publish

   **Depot Page:**
   - Title: `Depot`
   - Slug: `depot`
   - Content: (Leave blank)
   - Status: Publish

   **Artists Page:**
   - Title: `Artists`
   - Slug: `artists`
   - Content: (Leave blank)
   - Status: Publish

   **About Page:**
   - Title: `About`
   - Slug: `about`
   - Content: Add your gallery description here
   - Status: Publish

   **Contact Page:**
   - Title: `Contact`
   - Slug: `contact`
   - Content: Add contact information
   - Status: Publish

### Step 2: Create the Navigation Menu

1. Go to **Appearance → Menus**
2. Click **Create a new menu**
3. Name it: `Header Menu`
4. Under **Menu Settings**, check the box for **Header Menu** location
5. Click **Create Menu**

### Step 3: Add Pages to Menu

1. On the left, you'll see **Pages** - check all 6 pages you created
2. Click **Add to Menu**
3. Drag them into the correct order:
   - Home (first)
   - Collection
   - Depot
   - Artists
   - About
   - Contact (last)
4. Click **Save Menu**

### Step 4: Test the Menu

1. Make sure your React dev server is running:
   ```bash
   cd E:/projects/opus-gallery
   npm run dev
   ```

2. Visit http://localhost/gallery-template/

3. You should see the menu items from WordPress in the header

4. Check the API directly:
   - Visit: http://localhost/gallery-template/wp-json/opus/v1/menu
   - You should see JSON with your menu items

## How It Works

```
WordPress Admin
    ↓ (Create pages & menu)
WordPress Database
    ↓ (REST API)
/wp-json/opus/v1/menu
    ↓ (React Query)
useWPMenu() hook
    ↓ (Transform)
Header.tsx navigation
```

## Troubleshooting

**Menu not showing in React?**
- Check the API: http://localhost/gallery-template/wp-json/opus/v1/menu
- Make sure the menu is assigned to "Header Menu" location
- Check browser console for errors

**Wrong page loading?**
- Verify page slugs match the routes (e.g., `galleries` not `collection`)
- Check the slug in WordPress: Pages → Edit page → Permalink

**Menu shows but links don't work?**
- React Router handles client-side routing
- Make sure you're using the React dev server (npm run dev)
- Check that pages exist in the React app

## Next Steps

Now that the menu is dynamic, you can:
1. ✅ Add/remove menu items in WordPress admin
2. ✅ Reorder menu items by dragging
3. ✅ Rename menu labels without touching code
4. ✅ Create dropdown menus (parent/child)
5. Continue with content management (galleries, artworks, artists)
