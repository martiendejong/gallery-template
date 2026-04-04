import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/gallery';

test.describe('WordPress Integration', () => {
  test('Home page loads with WordPress data', async ({ page }) => {
    await page.goto(BASE_URL + '/');

    // Check the page loads and React mounts
    await expect(page.locator('#root')).toBeVisible();

    // Check hero section is present
    const heroOrContent = page.locator('h1').first();
    await expect(heroOrContent).toBeVisible();

    // Check React hydration — data-block-type attributes from server rendering
    const heroBlock = page.locator('[data-block-type="hero"]');
    if (await heroBlock.count() > 0) {
      await expect(heroBlock).toBeVisible();
    }
  });

  test('Galleries page loads', async ({ page }) => {
    await page.goto(BASE_URL + '/galleries');

    // Wait for React to load
    await expect(page.locator('#root')).toBeVisible();

    // Check page has content
    await page.waitForLoadState('networkidle');
    const body = await page.textContent('body');
    expect(body?.length).toBeGreaterThan(0);
  });

  test('Artists page loads', async ({ page }) => {
    await page.goto(BASE_URL + '/artists');

    await expect(page.locator('#root')).toBeVisible();
    await page.waitForLoadState('networkidle');
    const body = await page.textContent('body');
    expect(body?.length).toBeGreaterThan(0);
  });

  test('SEO meta tags are present', async ({ page }) => {
    await page.goto(BASE_URL + '/');

    // Check title
    const title = await page.title();
    expect(title).toContain('Opus');

    // Check description meta tag
    const description = page.locator('meta[name="description"]');
    await expect(description).toHaveAttribute('content');
  });

  test('REST API returns block data', async ({ request }) => {
    // Use query parameter style since pretty permalinks may not be configured
    const response = await request.get(BASE_URL + '/?rest_route=/lovable/v1/page/148');
    expect(response.ok()).toBeTruthy();

    const data = await response.json();
    expect(data).toHaveProperty('blocks');
    expect(data).toHaveProperty('layout');
    expect(data.blocks.length).toBeGreaterThan(0);
  });

  test('No console errors on page load', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    await page.goto(BASE_URL + '/');
    await page.waitForLoadState('networkidle');

    // Filter out known non-critical errors (e.g., favicon 404)
    const criticalErrors = errors.filter(e =>
      !e.includes('favicon') && !e.includes('404')
    );
    expect(criticalErrors).toHaveLength(0);
  });
});

test.describe('Visual Regression', () => {
  test('Homepage matches design', async ({ page }) => {
    await page.goto(BASE_URL + '/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000); // Wait for animations

    await expect(page).toHaveScreenshot('homepage.png', {
      fullPage: true,
      maxDiffPixelRatio: 0.05,
    });
  });
});
