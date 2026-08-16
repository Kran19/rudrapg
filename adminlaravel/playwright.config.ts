import { defineConfig, devices } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1, // To avoid SQLite lock issues, run sequentially
  reporter: [['html', { outputFolder: 'QA/reports' }]],
  outputDir: 'QA/raw-evidence', // Traces, videos, screenshots will end up here
  use: {
    baseURL: 'http://127.0.0.1:8088',
    trace: 'on',
    video: 'on',
    screenshot: 'on',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
    },
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    },
  ],
  webServer: {
    command: '"C:\\xampp\\php\\php.exe" artisan serve --port=8088',
    url: 'http://127.0.0.1:8088',
    reuseExistingServer: true,
    timeout: 120 * 1000,
  },
});
