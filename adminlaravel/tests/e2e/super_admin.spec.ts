import { test, expect } from '@playwright/test';

test.describe('Super Admin Flows', () => {
  test.beforeEach(async ({ page }) => {
    // Login as Super Admin
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/super-admin\/dashboard/);
  });

  test('can create a sub admin', async ({ page }) => {
    await page.goto('http://127.0.0.1:8000/super-admin/sub-admins');
    
    // Open modal
    await page.click('button:has-text("Add Sub Admin Account")');
    await expect(page.locator('form#create-subadmin-form')).toBeVisible();

    // Fill form
    const uniqueEmail = `playwright.${Date.now()}@rudrapg.com`;
    await page.fill('input[name="name"]', 'Playwright Tester');
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="phone"]', '99999' + Math.floor(10000 + Math.random() * 90000));
    await page.fill('input[name="password"]', 'password');
    
    // Check at least one branch
    const branchCheckboxes = page.locator('input[name="branches[]"]');
    if (await branchCheckboxes.count() > 0) {
      await branchCheckboxes.first().check();
    }

    // Submit
    await page.click('button:has-text("Create Account")');

    // Reload page to verify persistence in table
    await page.reload();
    await expect(page.locator(`.tabulator-row:has-text("${uniqueEmail}")`)).toBeVisible({ timeout: 15000 });
  });
});
