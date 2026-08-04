const { test, expect } = require('@playwright/test');

const BASE_URL = process.env.BASE_URL || 'http://localhost/Projet_Stage/Public';

test.describe('Authentication', () => {

  test('Register puis Login avec succès', async ({ page }) => {

    const email = `test_${Date.now()}@mail.com`;
    const password = 'TestPass123';

    // Register
    await page.goto(`${BASE_URL}/Register.php`);

    await page.locator('input[name="fullname"]').fill('Test User');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(password);
    await page.locator('input[name="confirm_password"]').fill(password);

    await Promise.all([
      page.waitForURL(/login\.php/i),
      page.locator('button[type="submit"]').click()
    ]);

    await expect(page).toHaveURL(/login\.php/i);

    // Login
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);

    await Promise.all([
      page.waitForURL(/dashboard\.php/i),
      page.locator('button[type="submit"]').click()
    ]);

    await expect(page).toHaveURL(/dashboard\.php/i);

  });

  test('Login avec un email inexistant', async ({ page }) => {

    await page.goto(`${BASE_URL}/login.php`);

    await page.locator('input[name="email"]').fill('inexistant@test.com');
    await page.locator('input[name="password"]').fill('WrongPassword');

    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/login\.php/i);

  });

  test('Login avec un mauvais mot de passe', async ({ page }) => {

    const email = `test_${Date.now()}@mail.com`;
    const password = 'TestPass123';

    // Register
    await page.goto(`${BASE_URL}/Register.php`);

    await page.locator('input[name="fullname"]').fill('Wrong Password User');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(password);
    await page.locator('input[name="confirm_password"]').fill(password);

    await Promise.all([
      page.waitForURL(/login\.php/i),
      page.locator('button[type="submit"]').click()
    ]);

    // Login avec mauvais mot de passe
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('WrongPassword');

    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/login\.php/i);

  });

});