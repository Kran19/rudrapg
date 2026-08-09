import { test, expect } from '@playwright/test';

test.describe('End-to-End Student Lifecycle', () => {
  let studentToken = '';
  const testPhone = '99988877' + Math.floor(10 + Math.random() * 90);
  const testEmail = `student${Math.floor(Math.random() * 10000)}@example.com`;

  test.describe.configure({ mode: 'serial' });

  test('1. Student API: Registers via QR', async ({ request }) => {
    const response = await request.post('http://127.0.0.1:8000/api/v1/student/register', {
      headers: { 'Accept': 'application/json' },
      data: {
        branch_code: 'PG-NRD-01', // Ensure this branch exists
        full_name: 'Playwright E2E Student',
        phone: testPhone,
        email: testEmail,
        password: 'password',
        password_confirmation: 'password',
        aadhaar_number: '123456789012',
        parent_name: 'Parent Name',
        parent_phone: '9988776655',
        current_address: 'E2E Test Address'
      }
    });

    const body = await response.json();
    console.log('Register Response:', body);
    expect(response.status()).toBe(201);
    expect(body.status).toBe('success');
  });

  test('2. Sub Admin UI: Verifies and Approves Student', async ({ page }) => {
    // Login
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[type="email"]', 'subadmin.naroda@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/sub-admin\/dashboard/);

    // Go to Verifications
    await page.goto('http://127.0.0.1:8000/sub-admin/verifications');

    // Find the student we just registered and approve
    const auditButton = page.locator('.tabulator-row:has-text("Playwright E2E Student") button:has-text("Audit")').first();
    
    // We expect the student to be in the table
    await expect(auditButton).toBeVisible({ timeout: 15000 });
    await page.waitForTimeout(1000); // Give Tabulator a second to settle
    await auditButton.click();

    // Assign Room/Bed if modal requires it (assuming modal has these fields)
    const approveButton = page.locator('button:has-text("Step 3: Approve & Key Handover")');
    await expect(approveButton).toBeVisible({ timeout: 15000 });
    await page.waitForTimeout(500); // Give Alpine transition a moment
    await approveButton.click();

    // Confirm SweetAlert
    const swalConfirm = page.locator('button.swal2-confirm');
    await expect(swalConfirm).toBeVisible({ timeout: 5000 });
    await swalConfirm.click();

    // Verify success toast
    await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
  });

  test('3. Student API: Logs in & Submits Reading and Complaint', async ({ request }) => {
    // Login to get Sanctum token
    const loginRes = await request.post('http://127.0.0.1:8000/api/v1/auth/login', {
      headers: { 'Accept': 'application/json' },
      data: {
        email: testEmail,
        password: 'password',
        device_name: 'playwright'
      }
    });
    expect(loginRes.status()).toBe(200);
    const loginData = await loginRes.json();
    studentToken = loginData.data.token;

    // Submit Electricity Reading
    const elecRes = await request.post('http://127.0.0.1:8000/api/v1/student/electricity-reading', {
      headers: { 
        'Authorization': `Bearer ${studentToken}`,
        'Accept': 'application/json'
      },
      data: {
        current_reading: 14500, // assuming previous was ~14475
      }
    });
    expect(elecRes.status()).toBe(200);

    // Submit Payment Proof
    const payRes = await request.post('http://127.0.0.1:8000/api/v1/student/payment-proof', {
      headers: { 
        'Authorization': `Bearer ${studentToken}`,
        'Accept': 'application/json'
      },
      data: {
        utr_number: 'UTR' + Date.now(),
        amount: 6500,
        payment_type: 'RENT'
      }
    });
    expect(payRes.status()).toBe(200);

    // Raise Complaint
    const compRes = await request.post('http://127.0.0.1:8000/api/v1/student/complaint', {
      headers: { 
        'Authorization': `Bearer ${studentToken}`,
        'Accept': 'application/json'
      },
      data: {
        category: 'PLUMBING',
        subject: 'Leaky Faucet',
        description: 'E2E test leaky faucet.'
      }
    });
    expect(compRes.status()).toBe(201);
  });

  test('4. Sub Admin UI: Audits Reading & Resolves Complaint', async ({ page }) => {
    // Login again
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[type="email"]', 'subadmin.naroda@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');

    // 4a. Audit Electricity
    await page.goto('http://127.0.0.1:8000/sub-admin/electricity-audit');
    const approveElecBtn = page.locator('.tabulator-row:has-text("Playwright E2E Student") button:has-text("Approve")').first();
    if (await approveElecBtn.isVisible()) {
        await approveElecBtn.click();
        await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    }

    // 4b. Resolve Complaint
    await page.goto('http://127.0.0.1:8000/sub-admin/complaints');
    const resolveBtn = page.locator('.tabulator-row:has-text("Playwright E2E Student") button:has-text("Resolve")').first();
    if (await resolveBtn.isVisible()) {
        await resolveBtn.click();
        await expect(page.locator('.toast-success')).toBeVisible({ timeout: 10000 });
    }
    
    // 4c. Verify Rent Ledger shows the payment
    await page.goto('http://127.0.0.1:8000/sub-admin/rent-ledger');
    // Ensure the student shows up with Paid or verify payment logic
    await expect(page.locator(`.tabulator-row:has-text("Playwright E2E Student")`)).toBeVisible();
  });
});
