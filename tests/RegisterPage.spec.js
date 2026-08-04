const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE || 'http://localhost/Projet_Stage';

test('register candidat', async ({ page }) => {
  const email = `cand_${Date.now()}@test.com`;
  
  await page.goto(`${BASE}/Public/Register.php`);
  await page.waitForSelector('input[name="fullname"]', { timeout: 5000 });
  
  await page.locator('input[name="fullname"]').fill('Candidat Test');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('select[name="role"]').selectOption('candidate');
  await page.locator('input[name="password"]').fill('Pass1234');
  await page.locator('input[name="confirm_password"]').fill('Pass1234');
  await page.locator('button[type="submit"]').click();
  
  await page.waitForURL(/login\.php/, { timeout: 5000 });
  expect(page.url()).toContain('login.php');
});

test('register recruteur', async ({ page }) => {
  const email = `comp_${Date.now()}@test.com`;
  
  await page.goto(`${BASE}/Public/Register.php`);
  await page.waitForSelector('input[name="fullname"]', { timeout: 5000 });
  
  await page.locator('input[name="fullname"]').fill('Company Test');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('select[name="role"]').selectOption('company');
  await page.locator('input[name="password"]').fill('Pass1234');
  await page.locator('input[name="confirm_password"]').fill('Pass1234');
  await page.locator('button[type="submit"]').click();
  
  await page.waitForURL(/login\.php/, { timeout: 5000 });
});

test('register password mismatch', async ({ page }) => {
  await page.goto(`${BASE}/Public/Register.php`);
  await page.waitForSelector('input[name="fullname"]', { timeout: 5000 });
  
  await page.locator('input[name="fullname"]').fill('Test Fail');
  await page.locator('input[name="email"]').fill(`fail_${Date.now()}@test.com`);
  await page.locator('input[name="password"]').fill('123456');
  await page.locator('input[name="confirm_password"]').fill('654321');
  await page.locator('button[type="submit"]').click();
  
  await page.waitForURL(/Register\.php/, { timeout: 5000 });
});