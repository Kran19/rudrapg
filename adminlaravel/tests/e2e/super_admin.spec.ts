import { test, expect } from '@playwright/test';

test.describe('Super Admin Flows', () => {
  test.beforeEach(async ({ page }) => {
    // Login as Super Admin
    await page.goto('http://127.0.0.1:8088/login');
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/super-admin\/dashboard/);
  });

  test('can create a sub admin', async ({ page }) => {
    await page.goto('http://127.0.0.1:8088/super-admin/sub-admins');
    
    // 1. Create a dedicated branch first to ensure an unassigned branch exists
    const testBranchCode = 'PG-SPEC-' + Math.floor(1000 + Math.random() * 9000);
    await page.goto('http://127.0.0.1:8088/super-admin/branches');
    await page.click('button:has-text("Add New PG Branch")');
    await page.fill('input[name="code"]', testBranchCode);
    await page.fill('input[name="name"]', 'Branch ' + testBranchCode);
    await page.fill('input[name="city"]', 'Ahmedabad');
    await page.fill('textarea[name="address"]', 'Test Address');
    await page.fill('input[name="phone"]', '9999900000');
    await page.fill('input[name="email"]', `spec.${Date.now()}@rudrapg.com`);
    await page.fill('input[name="electricity_unit_rate"]', '10');
    await page.fill('input[name="manager_name"]', 'Spec Manager');
    await page.fill('input[name="manager_phone"]', '8888800000');
    await page.click('button:has-text("Save Branch")');
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    await page.waitForTimeout(1000);

    // 2. Open Sub Admins page
    await page.goto('http://127.0.0.1:8088/super-admin/sub-admins');
    await page.click('button:has-text("Add Sub Admin Account")');
    await expect(page.locator('form#create-subadmin-form')).toBeVisible();

    // 3. Fill form
    const uniqueEmail = `playwright.${Date.now()}@rudrapg.com`;
    await page.fill('input[name="name"]', 'Playwright Tester');
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="phone"]', '99999' + Math.floor(10000 + Math.random() * 90000));
    await page.fill('input[name="password"]', 'password');
    
    // Check the newly created unassigned branch
    await page.locator(`#create-subadmin-form label:has-text("${testBranchCode}") input[type="checkbox"]`).first().check();

    // Submit
    await page.click('button:has-text("Create Account")');
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    await page.waitForTimeout(1500);

    // Verify persistence in table
    await expect(page.locator(`.tabulator-row:has-text("${uniqueEmail}")`)).toBeVisible({ timeout: 15000 });
  });
});

