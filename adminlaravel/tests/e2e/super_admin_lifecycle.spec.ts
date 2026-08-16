import { test, expect } from '@playwright/test';

test.describe('Super Admin Zero-Trust Lifecycle', () => {

  test('Complete Branch & Sub Admin Setup Flow', async ({ page, request }) => {
    test.setTimeout(60000);
    // -------------------------------------------------------------
    // Phase 1: Authentication
    // -------------------------------------------------------------
    await page.context().clearCookies();
    await page.goto('http://127.0.0.1:8088/login');
    
    // Login as Super Admin
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');

    // Verify Dashboard Access
    await expect(page.locator('text="Total Monthly Revenue"').first()).toBeVisible({ timeout: 15000 });
    
    // -------------------------------------------------------------
    // Phase 2: Create a New Branch
    // -------------------------------------------------------------
    await page.goto('http://127.0.0.1:8088/super-admin/branches');
    
    // Open Modal
    await page.click('button:has-text("Add New PG Branch")');
    
    // Generate dynamic branch code to prevent duplicates
    const timestamp = Date.now().toString().slice(-4);
    const branchCode = `PG-TEST-${timestamp}`;
    
    // Fill Branch Details
    await page.fill('input[name="code"]', branchCode);
    await page.fill('input[name="name"]', `Test Branch ${timestamp}`);
    await page.fill('input[name="city"]', 'Ahmedabad');
    await page.fill('textarea[name="address"]', '123 Automated Testing Road, Bopal');
    await page.fill('input[name="phone"]', '+91 99999 88888');
    await page.fill('input[name="email"]', `test.branch.${timestamp}@rudrapg.com`);
    await page.fill('input[name="electricity_unit_rate"]', '12');
    await page.fill('input[name="manager_name"]', 'Auto Manager');
    await page.fill('input[name="manager_phone"]', '+91 88888 77777');
    
    // Submit
    await page.click('button:has-text("Save Branch")');
    
    // Wait for Success Toast
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    await expect(page.locator(`text=${branchCode}`)).toBeVisible();

    // -------------------------------------------------------------
    // Phase 3: Generate Rooms & Beds for the Branch
    // -------------------------------------------------------------
    await page.goto('http://127.0.0.1:8088/super-admin/rooms-master');
    
    // Open Modal
    await page.click('button:has-text("Add New Room")');
    
    // Select the new branch (it might be the last option, or we select by label)
    await page.selectOption('select[name="branch_id"]', { label: `Test Branch ${timestamp} (${branchCode})` });
    
    await page.fill('input[name="room_number"]', '101-AUTO');
    await page.selectOption('select[name="floor_number"]', '1');
    await page.selectOption('select[name="sharing_type"]', '3 Sharing AC');
    await page.fill('input[name="max_beds"]', '3');
    // is_ac is a checkbox and checked by default
    await page.fill('input[name="rent"]', '7500');
    await page.fill('input[name="deposit"]', '15000');
    
    // Submit
    await page.click('button:has-text("Save Room")');
    
    // Wait for Success Toast and Reload
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    await page.waitForTimeout(1500);
    
    // Switch to 40-Room Visual Grid to view all rooms across floors
    await page.click('button:has-text("Switch to 40-Room Visual Grid")');
    await expect(page.locator('text="Room 101-AUTO"').first()).toBeVisible({ timeout: 15000 });

    // -------------------------------------------------------------
    // Phase 4: Create Sub Admin and Delegate Access
    // -------------------------------------------------------------
    await page.goto('http://127.0.0.1:8088/super-admin/sub-admins');
    
    // Open Modal
    await page.click('button:has-text("Add Sub Admin Account")');
    
    const subAdminEmail = `subadmin.${timestamp}@rudrapg.com`;
    
    await page.fill('input[name="name"]', `Auto SubAdmin ${timestamp}`);
    await page.fill('input[name="email"]', subAdminEmail);
    await page.fill('input[name="phone"]', '9999911111');
    await page.fill('input[name="password"]', 'password123');
    
    // Check the branch checkbox
    const branchBoxes = page.locator('input[name="branches[]"]');
    if (await branchBoxes.count() > 0) {
      await branchBoxes.first().check();
    }
    
    // Submit
    await page.click('button:has-text("Create Account")');
    
    // Verify Sub Admin created in table
    await expect(page.locator(`text=${subAdminEmail}`)).toBeVisible({ timeout: 15000 });

    // -------------------------------------------------------------
    // Phase 5: Verification (Login as new Sub Admin)
    // -------------------------------------------------------------
    // Since logout is a POST request in Laravel, we submit the logout form directly
    // to bypass SweetAlert2 confirm UI.
    await page.evaluate(() => {
        (document.getElementById('logout-form') as HTMLFormElement).submit();
    });
    
    // Wait for the navigation triggered by submit to finish
    await page.waitForURL('**/login');
    
    // Login as the new Sub Admin
    await page.fill('input[name="email"]', subAdminEmail);
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    
    // Should see Sub Admin Dashboard
    await expect(page.locator('text="Branch Occupancy"').first()).toBeVisible({ timeout: 10000 });
  });

});

