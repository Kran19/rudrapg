import { test, expect } from '@playwright/test';

test.describe('End-to-End Super Admin Lifecycle', () => {
  let superAdminToken = '';
  let newBranchId = '';
  let newSubAdminId = '';
  const testBranchEmail = `branch${Math.floor(Math.random() * 10000)}@example.com`;
  const testSubAdminEmail = `subadmin${Math.floor(Math.random() * 10000)}@example.com`;
  const testSubAdminPhone = '88877766' + Math.floor(10 + Math.random() * 90);

  test.describe.configure({ mode: 'serial' });

  test('1. API: Super Admin Logs In', async ({ request }) => {
    const loginRes = await request.post('http://127.0.0.1:8000/api/v1/auth/login', {
      headers: { 'Accept': 'application/json' },
      data: {
        email: 'admin@rudrapg.com',
        password: 'password',
        device_name: 'playwright'
      }
    });
    expect(loginRes.status()).toBe(200);
    const loginData = await loginRes.json();
    superAdminToken = loginData.data.token;
    expect(superAdminToken).toBeTruthy();
  });

  test('2. API: Setup New Branch', async ({ request }) => {
    const branchRes = await request.post('http://127.0.0.1:8000/api/v1/super-admin/branches', {
      headers: { 
        'Accept': 'application/json',
        'Authorization': `Bearer ${superAdminToken}`
      },
      data: {
        name: 'PG-TEST-01 Branch',
        address: '123 Test Street, QA City',
        city: 'QA City',
        phone: '1112223334',
        email: testBranchEmail,
        manager_name: 'QA Manager',
        manager_phone: '4445556667',
        electricity_unit_rate: 12.50
      }
    });
    
    expect(branchRes.status()).toBe(201);
    const branchData = await branchRes.json();
    expect(branchData.status).toBe('success');
    expect(branchData.data.name).toBe('PG-TEST-01 Branch');
    newBranchId = branchData.data.id;
  });

  test('3. API: Hire New Sub Admin', async ({ request }) => {
    const subAdminRes = await request.post('http://127.0.0.1:8000/api/v1/super-admin/sub-admins', {
      headers: { 
        'Accept': 'application/json',
        'Authorization': `Bearer ${superAdminToken}`
      },
      data: {
        name: 'Test Sub Admin',
        email: testSubAdminEmail,
        phone: testSubAdminPhone,
        password: 'password123',
        branch_ids: [newBranchId]
      }
    });

    expect(subAdminRes.status()).toBe(201);
    const subAdminData = await subAdminRes.json();
    expect(subAdminData.status).toBe('success');
    expect(subAdminData.data.email).toBe(testSubAdminEmail);
    newSubAdminId = subAdminData.data.id;
  });

  test('4. API: View KPIs and Logs', async ({ request }) => {
    const dashboardRes = await request.get('http://127.0.0.1:8000/api/v1/super-admin/dashboard', {
      headers: { 
        'Accept': 'application/json',
        'Authorization': `Bearer ${superAdminToken}`
      }
    });
    expect(dashboardRes.status()).toBe(200);
    const dashboardData = await dashboardRes.json();
    expect(dashboardData.status).toBe('success');
    // Ensure the structure returns numbers, e.g. total_branches
    expect(typeof dashboardData.data.total_branches).toBe('number');

    const logsRes = await request.get('http://127.0.0.1:8000/api/v1/super-admin/audit-logs', {
      headers: { 
        'Accept': 'application/json',
        'Authorization': `Bearer ${superAdminToken}`
      }
    });
    expect(logsRes.status()).toBe(200);
    const logsData = await logsRes.json();
    expect(logsData.status).toBe('success');
    expect(Array.isArray(logsData.data.data)).toBeTruthy();
  });

  test('5. UI: Super Admin Logs into Blade Dashboard', async ({ page }) => {
    // Navigate to web login
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[type="email"]', 'admin@rudrapg.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for the URL to route to Super Admin dashboard
    await page.waitForURL(/.*\/super-admin\/dashboard/);

    // Verify some text or element on the page ensuring it's Super Admin
    // For now we just verify the URL since blade views might be basic.
    expect(page.url()).toContain('/super-admin/dashboard');
  });
});
