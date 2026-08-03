import { test, expect } from '@playwright/test';

test.describe('Sub Admin Flows', () => {
  test.beforeEach(async ({ page }) => {
    // Login as Sub Admin
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[type="email"]', 'subadmin.naroda@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/sub-admin\/dashboard/);
  });

  test('can process pending student verifications', async ({ page }) => {
    await page.goto('http://127.0.0.1:8000/sub-admin/verifications');
    
    // Check if there's any pending verification to process
    const auditButton = page.locator('.tabulator-row button:has-text("Audit")').first();
    
    if (await auditButton.isVisible()) {
      await auditButton.click();
      
      // Wait for the modal and click approve
      const approveButton = page.locator('button:has-text("Approve Booking & Key Handover")');
      await expect(approveButton).toBeVisible();
      await approveButton.click();
      
      // Verify Toastr success
      await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    } else {
      // If none found, verify the empty state placeholder
      await expect(page.locator('.tabulator-placeholder:has-text("No Verification Requests Pending")')).toBeVisible();
    }
  });

  test('can record offline cash payment', async ({ page }) => {
    await page.goto('http://127.0.0.1:8000/sub-admin/rent-ledger');
    
    await page.click('button:has-text("Record Offline Cash Payment")');
    await expect(page.locator('#cash-payment-form')).toBeVisible();

    // Select first student
    await page.locator('select[name="student_id"]').selectOption({ index: 1 });
    await page.locator('select[name="payment_type"]').selectOption('RENT');
    await page.fill('input[name="amount"]', '6500');
    
    // Optionally put notes
    await page.fill('textarea[name="notes"]', 'Playwright Cash Payment');

    // Submit
    await page.click('button:has-text("Generate Receipt & Record Entry")');

    // Verify Toastr
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
  });
});
