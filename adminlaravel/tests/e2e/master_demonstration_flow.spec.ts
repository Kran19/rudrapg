import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

test.describe('Rudra PG Sequential Multi-Step Onboarding Flow', () => {
  const timestamp = Date.now();
  const studentPhone = `982${timestamp.toString().slice(-7)}`;
  const studentEmail = `aarav.multistep.${timestamp}@rudrapg.com`;
  let studentId: number;
  let appRef: string;
  let studentToken: string;

  const screenshotsDir = path.resolve(process.cwd(), 'QA/screenshots/master_flow');

  test.beforeAll(async () => {
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }
  });

  test('Execute Complete Sequential Multi-Step Flow with Gated Locks & Evidence', async ({ page, request }) => {
    test.setTimeout(120000);

    // ----------------------------------------------------------------------
    // STEP 1: Student Accesses App via Live QR Scanner Gate
    // ----------------------------------------------------------------------
    console.log('[Step 1] Student Accessing Live QR Scanner Gate Screen...');
    await page.goto('http://127.0.0.1:8085');
    await page.waitForTimeout(3000);
    await page.screenshot({ path: path.join(screenshotsDir, '01_student_flutter_portal.png'), fullPage: true });

    console.log('[Step 1a] Verifying Branch QR Code (PG-NRD-01)...');
    const qrVerifyRes = await request.post('http://127.0.0.1:8088/api/v1/branch/verify-qr', {
      headers: { 'Accept': 'application/json' },
      data: { qr_data: 'PG-NRD-01' }
    });
    expect(qrVerifyRes.status()).toBe(200);
    const qrVerifyData = await qrVerifyRes.json();
    expect(qrVerifyData.status).toBe('success');
    expect(qrVerifyData.data.code).toBe('PG-NRD-01');

    console.log('[Step 1b] Registering New Applicant with Aadhaar / KYC Details...');
    const regRes = await request.post('http://127.0.0.1:8088/api/v1/student/register', {
      headers: { 'Accept': 'application/json' },
      data: {
        branch_code: 'PG-NRD-01',
        full_name: 'Aarav Patel (Multi-Step Resident)',
        phone: studentPhone,
        email: studentEmail,
        password: 'password123',
        password_confirmation: 'password123',
        aadhaar_number: '987654321098',
        parent_name: 'Rajesh Patel',
        parent_phone: '9822233344',
        emergency_contact: '9822233344',
        current_address: '104, Shivalik High Street, Vastrapur, Ahmedabad',
      }
    });

    expect(regRes.status()).toBe(201);
    const regData = await regRes.json();
    studentId = regData.data.id;
    appRef = regData.data.app_reference;
    console.log(`Student registered: ID=${studentId}, AppRef=${appRef}`);

    // Verify initial rent_status is NOT_APPLICABLE (No bed allocated yet!)
    expect(regData.data.rent_status).toBe('NOT_APPLICABLE');
    expect(regData.data.is_bed_assigned).toBe(false);

    // ----------------------------------------------------------------------
    // STEP 2: Sub Admin Login & Verification Queue Audit
    // ----------------------------------------------------------------------
    console.log('[Step 2] Sub Admin Logging In...');
    await page.goto('http://127.0.0.1:8088/login');
    await page.fill('input[type="email"]', 'subadmin.naroda@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/sub-admin\/dashboard/);
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '02_sub_admin_login.png'), fullPage: true });

    // ----------------------------------------------------------------------
    // STEP 3: Sub Admin Verification Queue with Stepper Modal
    // ----------------------------------------------------------------------
    console.log('[Step 3] Sub Admin Inspecting Verification Queue...');
    await page.goto('http://127.0.0.1:8088/sub-admin/verifications');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '03_sub_admin_verifications_queue.png'), fullPage: true });

    // Open Audit Modal for newly registered student
    const auditBtn = page.locator('button.audit-btn').first();
    if (await auditBtn.count() > 0) {
      await auditBtn.click();
      await page.waitForTimeout(1000);
      await page.screenshot({ path: path.join(screenshotsDir, '04_sub_admin_student_kyc_modal.png'), fullPage: true });
    }

    // ----------------------------------------------------------------------
    // STEP 4: Sub Admin Executes Step 1 (Approve Profile KYC)
    // ----------------------------------------------------------------------
    console.log('[Step 4] Sub Admin Executing Step 1: Approve Profile KYC...');
    const kycResult = await page.evaluate(async (ref) => {
      const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
      const res = await fetch(`/sub-admin/verifications/${ref}/approve-kyc`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' }
      });
      return res.json();
    }, appRef);
    expect(kycResult.status).toBe('success');
    console.log(`Step 1 Result: ${kycResult.message}`);
    await page.reload();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '04_sub_admin_kyc_approved.png'), fullPage: true });

    // ----------------------------------------------------------------------
    // STEP 5: Sub Admin Executes Step 2 (Assign Room & Bed)
    // ----------------------------------------------------------------------
    console.log('[Step 5] Sub Admin Executing Step 2: Allocate Room & Bed...');
    const bedResult = await page.evaluate(async ({ ref }) => {
      const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
      const availableBeds = (window as any).availableBedsData || [];
      const targetBedId = availableBeds.length > 0 ? availableBeds[0].id : 2;
      const res = await fetch(`/sub-admin/verifications/${ref}/assign-bed`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
        body: JSON.stringify({ bed_id: targetBedId })
      });
      return res.json();
    }, { ref: appRef });
    expect(bedResult.status).toBe('success');
    console.log(`Step 2 Result: ${bedResult.message}`);
    await page.reload();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '05_sub_admin_bed_allocation.png'), fullPage: true });

    // ----------------------------------------------------------------------
    // STEP 6: Student Submits Rent & Deposit Payment Proof
    // ----------------------------------------------------------------------
    console.log('[Step 6] Student Submitting Rent & Deposit Payment Proof...');
    // Login as student to get token
    const loginRes = await request.post('http://127.0.0.1:8088/api/v1/auth/login', {
      headers: { 'Accept': 'application/json' },
      data: { phone: studentPhone, password: 'password123' }
    });
    expect(loginRes.status()).toBe(200);
    const loginData = await loginRes.json();
    studentToken = loginData.data.token;

    // Verify student profile reflects Bed Allocation and Due calculation
    const profileRes = await request.get('http://127.0.0.1:8088/api/v1/student/profile', {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${studentToken}` }
    });
    expect(profileRes.status()).toBe(200);
    const profileData = await profileRes.json();
    expect(profileData.data.is_bed_assigned).toBe(true);
    expect(profileData.data.rent_status).toBe('DUE');
    console.log(`Student Profile: Bed=${profileData.data.bed?.bed_code}, Rent=₹${profileData.data.bed?.monthly_rent}, Status=${profileData.data.rent_status}`);

    // Submit Payment Proof with UTR
    const utrNumber = `UPI987654${timestamp.toString().slice(-6)}`;
    const payProofRes = await request.post('http://127.0.0.1:8088/api/v1/student/payment-proof', {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${studentToken}` },
      data: {
        utr_number: utrNumber,
        payment_type: 'RENT_AND_DEPOSIT',
        amount: 16500.00
      }
    });
    expect(payProofRes.status()).toBe(200);
    console.log(`Payment Proof Submitted: UTR=${utrNumber}`);

    // Verify student profile rent_status is now UNDER_VERIFICATION
    const profileAfterPayRes = await request.get('http://127.0.0.1:8088/api/v1/student/profile', {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${studentToken}` }
    });
    const profileAfterPayData = await profileAfterPayRes.json();
    expect(profileAfterPayData.data.rent_status).toBe('UNDER_VERIFICATION');
    console.log(`Student Rent Status updated to: ${profileAfterPayData.data.rent_status}`);

    // ----------------------------------------------------------------------
    // STEP 7: Sub Admin Executes Step 3 (Payment Verification & Key Handover)
    // ----------------------------------------------------------------------
    console.log('[Step 7] Sub Admin Executing Step 3: Approve Payment & Key Handover...');
    const handoverResult = await page.evaluate(async ({ ref }) => {
      const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
      const res = await fetch(`/sub-admin/verifications/${ref}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' }
      });
      return res.json();
    }, { ref: appRef });
    expect(handoverResult.status).toBe('success');
    console.log(`Step 3 Result: ${handoverResult.message}`);

    // ----------------------------------------------------------------------
    // STEP 8: Verification of Active Resident State
    // ----------------------------------------------------------------------
    const activeProfileRes = await request.get('http://127.0.0.1:8088/api/v1/student/profile', {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${studentToken}` }
    });
    const activeProfileData = await activeProfileRes.json();
    expect(activeProfileData.data.status).toBe('APPROVED');
    expect(activeProfileData.data.rent_status).toBe('PAID');
    expect(activeProfileData.data.is_fully_approved).toBe(true);
    console.log(`Resident is now FULLY ACTIVE: Status=${activeProfileData.data.status}, RentStatus=${activeProfileData.data.rent_status}`);

    // ----------------------------------------------------------------------
    // STEP 9: Sub Admin Bed Map & Rent Ledger Dashboards
    // ----------------------------------------------------------------------
    console.log('[Step 9] Sub Admin Checking Interactive Bed Map...');
    await page.goto('http://127.0.0.1:8088/sub-admin/bed-map');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '05_sub_admin_bed_allocation.png'), fullPage: true });
    await page.screenshot({ path: path.join(screenshotsDir, '06_sub_admin_bed_map.png'), fullPage: true });

    console.log('[Step 9b] Sub Admin Checking Rent Ledger...');
    await page.goto('http://127.0.0.1:8088/sub-admin/rent-ledger');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '07_sub_admin_rent_ledger.png'), fullPage: true });

    console.log('[Step 9c] Sub Admin Checking Electricity Audit...');
    await page.goto('http://127.0.0.1:8088/sub-admin/electricity');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '08_sub_admin_electricity_audit.png'), fullPage: true });

    console.log('[Step 9d] Sub Admin Checking Complaints Resolution Desk...');
    await page.goto('http://127.0.0.1:8088/sub-admin/complaints');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '09_sub_admin_complaints_resolved.png'), fullPage: true });

    // ----------------------------------------------------------------------
    // STEP 10: Super Admin Oversight & Standees
    // ----------------------------------------------------------------------
    console.log('[Step 10] Super Admin Logging In...');
    await page.context().clearCookies();
    await page.goto('http://127.0.0.1:8088/login');
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*\/super-admin\/dashboard/);
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '10_super_admin_dashboard.png'), fullPage: true });

    console.log('[Step 10a] Super Admin Inspecting Branches & QR Standees...');
    await page.goto('http://127.0.0.1:8088/super-admin/branches');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '11_super_admin_branches.png'), fullPage: true });

    const qrBtn = page.locator('button[onclick*="showQrModal"]').first();
    if (await qrBtn.count() > 0) {
      await qrBtn.click();
      await page.waitForTimeout(1000);
      await page.screenshot({ path: path.join(screenshotsDir, '11b_super_admin_branch_qr_standee.png'), fullPage: true });
    }

    console.log('[Step 10b] Super Admin Inspecting Finance Master Ledger...');
    await page.goto('http://127.0.0.1:8088/super-admin/finance');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: path.join(screenshotsDir, '15_super_admin_finance.png'), fullPage: true });

    console.log('✅ Sequential Multi-Step Onboarding Journey executed successfully with 100% assertions passing!');
  });
});
