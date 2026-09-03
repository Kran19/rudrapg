import { test, expect } from '@playwright/test';

test.describe('Sub Admin Flows', () => {
  test.beforeEach(async ({ page }) => {
    // Login as Sub Admin
    await page.goto('http://127.0.0.1:8088/login');
    await page.fill('input[type="email"]', 'subadmin.naroda@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/sub-admin\/dashboard/);
  });

  test('can process pending student verifications', async ({ page }) => {
    await page.goto('http://127.0.0.1:8088/sub-admin/verifications');
    
    // Check if there's any pending verification to process
    const auditButton = page.locator('.tabulator-row button:has-text("Audit")').first();
    
    if (await auditButton.isVisible()) {
      await auditButton.click();
      
      // Wait for modal to render
      await expect(page.locator('text="Applicant Audit Desk:"')).toBeVisible({ timeout: 10000 });
      
      // Check which approval action is pending (Step 1 KYC or Step 3 Key Handover)
      const kycApproveBtn = page.locator('button:has-text("Step 1: Approve Profile KYC")');
      const step3ApproveBtn = page.locator('button:has-text("Step 3: Approve & Key Handover")');

      if (await kycApproveBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
        await kycApproveBtn.click();
        const swal = page.locator('.swal2-confirm');
        if (await swal.isVisible({ timeout: 2000 }).catch(() => false)) {
          await swal.click();
        }
        await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
      } else if (await step3ApproveBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
        await step3ApproveBtn.click();
        const swal = page.locator('.swal2-confirm');
        if (await swal.isVisible({ timeout: 2000 }).catch(() => false)) {
          await swal.click();
        }
        await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
      }
    } else {
      // If no pending audit buttons, verify the verifications table container is rendered cleanly
      await expect(page.locator('#verifications-table')).toBeVisible();
    }
  });

  test('can record offline cash payment', async ({ page }) => {
    await page.goto('http://127.0.0.1:8088/sub-admin/rent-ledger');
    
    await page.click('button:has-text("Record Offline Cash Payment")');
    await expect(page.locator('#record-cash-form')).toBeVisible();

    // Select first student
    await page.locator('select[name="student_id"]').selectOption({ index: 0 });
    await page.locator('select[name="payment_type"]').selectOption('RENT');
    await page.fill('input[name="amount"]', '6500');
    
    // Optionally put notes
    await page.fill('input[name="remarks"]', 'Playwright Cash Payment');

    // Submit
    await page.click('#record-cash-form button[type="submit"]');

    // Verify Toastr
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
  });
});

