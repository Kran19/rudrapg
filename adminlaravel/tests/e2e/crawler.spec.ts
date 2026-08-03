import { test, expect } from '@playwright/test';

test.describe('Autonomous Crawler & Evidence Generator', () => {
  const visitedUrls = new Set<string>();
  const toVisitUrls = new Set<string>();

  test('Crawl and verify all reachable routes', async ({ page, context }) => {
    const baseUrl = 'http://127.0.0.1:8000';
    
    // Login to access protected routes
    await page.goto(`${baseUrl}/login`);
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/super-admin\/dashboard/);

    toVisitUrls.add(`${baseUrl}/super-admin/dashboard`);

    while (toVisitUrls.size > 0) {
      const urlsArray = Array.from(toVisitUrls);
      const url = urlsArray[0];
      toVisitUrls.delete(url);
      
      if (visitedUrls.has(url)) continue;
      visitedUrls.add(url);

      console.log(`Crawling: ${url}`);
      
      const response = await page.goto(url, { waitUntil: 'networkidle' });
      
      // Verification 1: HTTP 200
      expect(response?.status()).toBe(200);

      // Collect links on the page
      const hrefs = await page.$$eval('a', links => links.map(a => a.href));
      for (const href of hrefs) {
        if (href.startsWith(baseUrl) && !href.includes('logout') && !href.includes('#')) {
          if (!visitedUrls.has(href)) {
            toVisitUrls.add(href);
          }
        }
      }
    }
  });
});
