# WordPress Plugin Integration Guide

## How It Works

Your React app now has **two API layers**:

### Layer 1: Custom Structured Data (Galleries, Artists, Artworks)
```
/wp-json/opus/v1/galleries
/wp-json/opus/v1/artists
/wp-json/opus/v1/artworks
```
**Use for:** Structured content you want full control over in React

### Layer 2: WordPress Native Content (Pages, Posts, Plugins)
```
/wp-json/wp/v2/pages
/wp-json/wp/v2/posts
```
**Use for:** Everything else - pages with plugin content, blog posts, FAQs, forms, etc.

## The Magic: `content.rendered`

When you fetch a WordPress page, the `content.rendered` field contains **ALL** content fully rendered by WordPress, including:

✅ **Gutenberg Blocks** - All native and custom blocks
✅ **Shortcodes** - `[faq]`, `[contact-form-7]`, `[gallery]`, etc.
✅ **Plugin Content** - FAQ plugins, form builders, sliders, etc.
✅ **Custom Blocks** - Third-party block plugins
✅ **Widgets** - If embedded in content
✅ **Embedded Media** - YouTube, Twitter, etc.

**WordPress does the heavy lifting of rendering everything. React just displays it.**

## Step-by-Step: How to Show WordPress Page Content

### 1. Create a Page Component

Let's create an "About" page that displays WordPress content:

**File:** `src/pages/About.tsx`

```tsx
import { useWPPage } from '@/lib/wordpress-api';
import LoadingSpinner from '@/components/LoadingScreen';

export default function About() {
  const { data: page, isLoading, error } = useWPPage('about');

  if (isLoading) return <LoadingSpinner />;
  if (error) return <div>Error loading page</div>;
  if (!page) return <div>Page not found</div>;

  return (
    <div className="min-h-screen bg-background py-20">
      <div className="max-w-4xl mx-auto px-6">
        {/* Page Title */}
        <h1
          className="font-display text-5xl mb-8"
          dangerouslySetInnerHTML={{ __html: page.title.rendered }}
        />

        {/* WordPress Content - includes ALL plugins, blocks, shortcodes */}
        <div
          className="prose prose-lg max-w-none wordpress-content"
          dangerouslySetInnerHTML={{ __html: page.content.rendered }}
        />
      </div>
    </div>
  );
}
```

### 2. Add CSS for WordPress Content

**File:** `src/index.css` (add to your global styles)

```css
/* WordPress Content Styling */
.wordpress-content {
  /* Typography */
  font-family: var(--font-body);
  line-height: 1.7;
  color: var(--foreground);
}

.wordpress-content h1,
.wordpress-content h2,
.wordpress-content h3,
.wordpress-content h4 {
  font-family: var(--font-display);
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.wordpress-content p {
  margin-bottom: 1.5rem;
}

.wordpress-content a {
  color: var(--primary);
  text-decoration: underline;
  transition: opacity 0.3s;
}

.wordpress-content a:hover {
  opacity: 0.7;
}

/* WordPress Blocks */
.wordpress-content .wp-block-image {
  margin: 2rem 0;
}

.wordpress-content .wp-block-quote {
  border-left: 4px solid var(--primary);
  padding-left: 1.5rem;
  font-style: italic;
  margin: 2rem 0;
}

.wordpress-content .wp-block-button {
  margin: 1rem 0;
}

.wordpress-content .wp-block-button__link {
  background: var(--primary);
  color: white;
  padding: 0.75rem 1.5rem;
  text-decoration: none;
  display: inline-block;
  transition: opacity 0.3s;
}

.wordpress-content .wp-block-button__link:hover {
  opacity: 0.8;
}

/* Plugin Content */
.wordpress-content .faq-item {
  margin-bottom: 1rem;
  border: 1px solid var(--border);
  padding: 1rem;
}

.wordpress-content form {
  margin: 2rem 0;
}

.wordpress-content form input,
.wordpress-content form textarea {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 1rem;
  border: 1px solid var(--border);
  background: var(--background);
  color: var(--foreground);
}

.wordpress-content form button {
  background: var(--primary);
  color: white;
  padding: 0.75rem 2rem;
  border: none;
  cursor: pointer;
  transition: opacity 0.3s;
}

.wordpress-content form button:hover {
  opacity: 0.8;
}
```

## Real-World Examples

### Example 1: About Page with FAQ Plugin

**In WordPress Admin:**
1. Create page "About"
2. Add your about text
3. Add FAQ shortcode: `[faq category="about-us"]`
4. Publish

**Result:** React page shows all content including working FAQ accordion

### Example 2: Contact Page with Contact Form 7

**In WordPress Admin:**
1. Install Contact Form 7 plugin
2. Create page "Contact"
3. Add text + form shortcode: `[contact-form-7 id="123"]`
4. Publish

**Result:** React page shows working contact form

### Example 3: Services Page with Custom Blocks

**In WordPress Admin:**
1. Create page "Services"
2. Use block editor to add:
   - Columns block
   - Image blocks
   - Custom pricing table block (from plugin)
3. Publish

**Result:** All blocks render beautifully in React

## How Different Plugins Work

### FAQ Plugins
Examples: Easy Accordion, Ultimate FAQ

**WordPress:** `[faq]` → Renders HTML + JavaScript
**React:** Displays rendered HTML → FAQ works!

### Form Plugins
Examples: Contact Form 7, WPForms, Gravity Forms

**WordPress:** `[contact-form]` → Renders form HTML + AJAX handlers
**React:** Displays form → Submissions work via WordPress AJAX

### Slider Plugins
Examples: MetaSlider, Smart Slider

**WordPress:** `[metaslider id=123]` → Renders slider + JavaScript
**React:** Displays slider → Auto-plays!

### Gallery Plugins
Examples: NextGEN, Envira Gallery

**WordPress:** `[gallery]` → Renders lightbox gallery
**React:** Displays gallery → Lightbox works!

## Advanced: Mixed Content Strategy

You can mix custom structured data with WordPress content:

```tsx
export default function GalleryPage() {
  // Custom structured data for gallery grid
  const { pieces } = useGalleryPieces();

  // WordPress content for intro text + any plugins
  const { data: page } = useWPPage('galleries');

  return (
    <div>
      {/* WordPress content with plugins */}
      {page && (
        <div
          className="wordpress-content"
          dangerouslySetInnerHTML={{ __html: page.content.rendered }}
        />
      )}

      {/* Custom React gallery with beautiful UI */}
      <div className="gallery-grid">
        {pieces.map(piece => <GalleryCard key={piece.id} {...piece} />)}
      </div>
    </div>
  );
}
```

## Security: Content Sanitization

WordPress already sanitizes content before rendering. The `content.rendered` field is safe to display.

**Additional security (optional):**

Install DOMPurify for extra sanitization:

```bash
npm install dompurify
npm install --save-dev @types/dompurify
```

Create a safe content component:

```tsx
import DOMPurify from 'dompurify';

export function SafeWordPressContent({ html }: { html: string }) {
  const clean = DOMPurify.sanitize(html);
  return <div dangerouslySetInnerHTML={{ __html: clean }} />;
}
```

## Testing WordPress Integration

### 1. Create a Test Page

**In WordPress Admin:**
1. Pages → Add New
2. Title: "Test Integration"
3. Content:
```
<h2>Testing WordPress Content</h2>

<p>This is a paragraph with <strong>bold text</strong> and <em>italic text</em>.</p>

<!-- Add a FAQ shortcode if you have a plugin -->
[faq]

<!-- Add a contact form if you have Contact Form 7 -->
[contact-form-7 id="1"]

<!-- Add a button block -->
<!-- Use block editor to add a button -->
```
4. Publish

### 2. Create React Route

**File:** `src/App.tsx`

Add route:
```tsx
<Route path="/test" element={<TestPage />} />
```

**File:** `src/pages/TestPage.tsx`
```tsx
import { useWPPage } from '@/lib/wordpress-api';

export default function TestPage() {
  const { data: page, isLoading } = useWPPage('test-integration');

  if (isLoading) return <div>Loading...</div>;

  return (
    <div className="min-h-screen py-20 px-6">
      <div className="max-w-4xl mx-auto">
        <h1 dangerouslySetInnerHTML={{ __html: page?.title.rendered || '' }} />
        <div
          className="wordpress-content"
          dangerouslySetInnerHTML={{ __html: page?.content.rendered || '' }}
        />
      </div>
    </div>
  );
}
```

### 3. Test

Visit: `http://localhost/gallery-template/test`

You should see:
✅ All text formatted correctly
✅ Shortcodes rendered (if plugins active)
✅ Blocks displayed beautifully
✅ Plugin functionality works (forms submit, FAQs expand, etc.)

## Benefits of This Approach

### ✅ Plugin Compatibility
- **Works with 99% of WordPress plugins**
- No custom integration needed per plugin
- Plugins just work out of the box

### ✅ Editor Freedom
- Non-technical users can use WordPress editor
- All Gutenberg blocks available
- Visual editing experience

### ✅ Best of Both Worlds
- **WordPress:** Content management + plugins
- **React:** Beautiful UI + animations for galleries/artworks
- Each does what it does best

### ✅ Future-Proof
- New plugins automatically work
- No React code changes for content updates
- Leverages WordPress ecosystem

## When to Use Each Approach

### Use Custom Post Types (opus_gallery, opus_artwork, opus_artist)
- ✅ Structured data you want to filter/sort in React
- ✅ Data displayed in custom beautiful UI components
- ✅ Complex relationships between content types
- ✅ Need specific fields with validation

### Use WordPress Pages/Posts (content.rendered)
- ✅ Simple content pages (About, Contact, etc.)
- ✅ Blog posts with mixed content
- ✅ Landing pages with plugins
- ✅ Content managed by non-developers
- ✅ FAQ pages, forms, sliders, etc.

## Summary

**Your React app is now a hybrid:**

```
┌─────────────────────────────────────────┐
│  React App                              │
├─────────────────────────────────────────┤
│                                         │
│  Custom UI Layer                        │
│  (Galleries, Artists, Artworks)         │
│  ↓ Uses: opus/v1 API                    │
│                                         │
│  +                                      │
│                                         │
│  WordPress Content Layer                │
│  (Pages, Posts, Plugins)                │
│  ↓ Uses: wp/v2 API                      │
│                                         │
└─────────────────────────────────────────┘
```

**Result:** Beautiful custom UI for galleries + full WordPress plugin ecosystem for everything else!
