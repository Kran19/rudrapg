import { test as setup } from '@playwright/test';
import { execSync } from 'child_process';

setup('reset and seed database', async () => {
  console.log('Running database reset and seed...');
  try {
    const phpBin = process.platform === 'win32' ? '"C:\\xampp\\php\\php.exe"' : 'php';
    execSync(`${phpBin} artisan migrate:fresh --seed`, { stdio: 'inherit' });
    console.log('Database reset complete.');
  } catch (e) {
    console.error('Failed to run migration', e);
    throw e;
  }
});
