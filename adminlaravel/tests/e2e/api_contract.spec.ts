import { test, expect } from '@playwright/test';

test.describe('API Contract Regression', () => {
  let authToken = '';

  test('Super Admin can authenticate via Sanctum', async ({ request }) => {
    const response = await request.post('http://127.0.0.1:8000/api/v1/auth/login', {
      data: {
        email: 'admin@rudrapg.com',
        password: 'password',
        device_name: 'playwright-tests'
      }
    });

    expect(response.status()).toBe(200);
    const json = await response.json();
    expect(json.data).toHaveProperty('token');
    authToken = json.data.token;
  });

  test('Super Admin can fetch API dashboard KPI metrics', async ({ request }) => {
    expect(authToken).not.toBe('');
    
    const response = await request.get('http://127.0.0.1:8000/api/v1/super-admin/dashboard', {
      headers: {
        Authorization: `Bearer ${authToken}`
      }
    });

    expect(response.status()).toBe(200);
    const json = await response.json();
    expect(json).toHaveProperty('status', 'success');
    expect(json.data).toHaveProperty('total_branches');
  });
});
