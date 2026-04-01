# WordPress Theme Activation Checklist

## Issue: Menu Still Static

The React app shows the hardcoded menu because WordPress hasn't been fully set up yet.

## Step-by-Step Activation

### Step 1: Verify WordPress Installation

1. Visit: http://localhost/gallery-template/wp-admin
2. Log in to WordPress admin

**If you see a WordPress installation screen:**
- Complete the WordPress installation
- Set up admin user and password
- Continue to Step 2

**If you're already logged in:**
- Continue to Step 2

### Step 2: Activate the OPUS Gallery Theme

1. Go to **Appearance → Themes**
2. You should see three themes:
   - Twenty Twenty-Four (or similar)
   - **OPUS Gallery** ← This is our theme!
   - Others...
3. Click **Activate** on "OPUS Gallery"

**Expected result:**
- Message: "New theme activated"
- Theme shows as "Active"

### Step 3: Test the REST API

Open these URLs in your browser (should return JSON, not HTML):

**Test 1 - Custom API:**
```
http://localhost/gallery-template/wp-json/opus/v1/galleries
```
**Expected:** `[]` (empty array - no galleries yet)

**Test 2 - Menu API:**
```
http://localhost/gallery-template/wp-json/opus/v1/menu
```
**Expected:** `[]` (empty array - no menu yet)

**Test 3 - WordPress Pages:**
```
http://localhost/gallery-template/wp-json/wp/v2/pages
```
**Expected:** JSON array of pages

**If you see HTML instead of JSON:**
- Go to **Settings → Permalinks**
- Click "Save Changes" (even without changing anything)
- This flushes WordPress rewrite rules
- Test the URLs again

### Step 4: Create the Navigation Menu

1. Go to **Appearance → Menus**
2. Click **create a new menu**
3. Menu Name: `Header Menu`
4. Click **Create Menu**
5. Under **Menu Settings**, check the box: ☑ **Header Menu**
6. Click **Save Menu**

**Important:** The menu location must be named exactly "Header Menu" or "header-menu"

### Step 5: Create Pages

1. Go to **Pages → Add New**

Create these 6 pages (one by one):

**Page 1:**
- Title: `Home`
- Content: (leave blank or add welcome text)
- Click **Publish**

**Page 2:**
- Title: `Collection`
- URL Slug: Change to `galleries` (important!)
- Content: (leave blank)
- Click **Publish**

**Page 3:**
- Title: `Depot`
- Content: (leave blank)
- Click **Publish**

**Page 4:**
- Title: `Artists`
- Content: (leave blank)
- Click **Publish**

**Page 5:**
- Title: `About`
- Content: Add your about text
- Click **Publish**

**Page 6:**
- Title: `Contact`
- Content: Add contact information
- Click **Publish**

### Step 6: Add Pages to Menu

1. Go back to **Appearance → Menus**
2. Select "Header Menu" from dropdown (if not already selected)
3. On the left side, find **Pages** box
4. Check all 6 pages you just created
5. Click **Add to Menu**
6. Drag them in this order:
   - Home (at top)
   - Collection
   - Depot
   - Artists
   - About
   - Contact (at bottom)
7. Click **Save Menu**

### Step 7: Verify Menu API

Open in browser:
```
http://localhost/gallery-template/wp-json/opus/v1/menu
```

**Expected result:**
```json
[
  {
    "id": 1,
    "title": "Home",
    "url": "http://localhost/gallery-template/home/",
    "slug": "home",
    "target": "",
    "parent": 0,
    "order": 1
  },
  {
    "id": 2,
    "title": "Collection",
    ...
  }
]
```

**If you see `[]` (empty array):**
- Go back to **Appearance → Menus**
- Make sure "Header Menu" location is checked
- Save menu again

### Step 8: Test React App

1. Make sure React dev server is running:
```bash
cd E:/projects/opus-gallery
npm run dev
```

2. Visit: http://localhost/gallery-template/

**You should see:**
✅ Menu items from WordPress (not hardcoded)
✅ Menu items in correct order
✅ All 6 pages linked

3. Open browser console (F12) and check for errors

**If menu is still hardcoded:**
- Check browser console for API errors
- Verify the API URL is correct in console: `window.wpData`
- Check React Query DevTools (if installed)

### Step 9: Rebuild React App (If Needed)

If you're testing production build instead of dev server:

```bash
cd E:/projects/opus-gallery
npm run build
```

Then hard refresh browser: `Ctrl + Shift + R`

## Troubleshooting

### Problem: REST API returns HTML instead of JSON

**Solution:**
1. Settings → Permalinks
2. Click "Save Changes"
3. Test API again

### Problem: Menu API returns empty array `[]`

**Solution:**
1. Verify menu is assigned to "Header Menu" location
2. Check that pages are actually in the menu
3. Save menu again

### Problem: React shows hardcoded menu

**Reasons:**
1. WordPress menu API returns empty array → Create menu
2. React dev server not running → Run `npm run dev`
3. CORS issues → Check browser console for errors
4. Theme not activated → Activate theme

**Debug:**
```javascript
// Open browser console (F12) and type:
window.wpData
// Should show: { apiUrl: "...", wpApiUrl: "...", ... }

// Check what menu API returns:
fetch('http://localhost/gallery-template/wp-json/opus/v1/menu')
  .then(r => r.json())
  .then(console.log)
```

### Problem: Theme doesn't appear in WordPress

**Check symlink/junction:**
```bash
# On Windows, check if junction exists:
dir "E:\websites\gallery-template\wp-content\themes\"

# Should show: opus-gallery [JUNCTION]
```

**If missing, recreate:**
```bash
mklink /J "E:\websites\gallery-template\wp-content\themes\opus-gallery" "E:\projects\gallery-template\theme"
```

## Quick Verification Checklist

Before expecting the menu to work, verify:

- [ ] WordPress is installed at http://localhost/gallery-template/
- [ ] Can log in to wp-admin
- [ ] OPUS Gallery theme is activated (Appearance → Themes)
- [ ] REST API works (test URLs return JSON, not HTML)
- [ ] Menu is created and assigned to "Header Menu" location
- [ ] Pages are created and added to menu
- [ ] Menu API returns array of menu items (not empty array)
- [ ] React dev server is running (npm run dev)
- [ ] Browser shows React app (not WordPress theme)

## Expected Flow

```
1. User visits http://localhost/gallery-template/
   ↓
2. WordPress loads OPUS Gallery theme
   ↓
3. Theme's index.php loads React app
   ↓
4. React app reads window.wpData.apiUrl
   ↓
5. useWPMenu() hook fetches from /opus/v1/menu
   ↓
6. WordPress returns menu items as JSON
   ↓
7. React displays menu items in Header
   ↓
8. Dynamic menu working! 🎉
```

## Still Not Working?

If you've completed all steps and it's still showing the hardcoded menu:

1. **Check what the API returns:**
   - Open: http://localhost/gallery-template/wp-json/opus/v1/menu
   - Should see JSON array with menu items
   - If empty array `[]` → menu not set up correctly
   - If HTML → REST API issue

2. **Check browser console:**
   - Press F12
   - Look for red errors
   - Check Network tab for API calls

3. **Check window.wpData:**
   - In console, type: `window.wpData`
   - Should show apiUrl and wpApiUrl

4. **Verify React Query is fetching:**
   - In console: `localStorage.getItem('REACT_QUERY_OFFLINE_CACHE')`
   - Should show cached queries

Let me know which step fails and I'll help debug!
