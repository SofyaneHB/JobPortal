const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE || 'http://localhost/Projet_Stage';

test('company section smoke test', async ({ page }) => {
	const stamp = Date.now();
	const email = `company_${stamp}@test.com`;
	const password = 'TestPass123!';

	await page.goto(`${BASE}/Public/Register.php`);
	await page.locator('input[name="fullname"]').fill('Company Playwright');
	await page.locator('input[name="email"]').fill(email);
	await page.locator('select[name="role"]').selectOption('company');
	await page.locator('input[name="password"]').fill(password);
	await page.locator('input[name="confirm_password"]').fill(password);
	await page.locator('button[type="submit"]').click();

	await page.goto(`${BASE}/Public/login.php`);
	await page.locator('input[name="email"]').fill(email);
	await page.locator('input[name="password"]').fill(password);
	await page.locator('button[type="submit"]').click();

	await expect(page).toHaveURL(/company\/dashboard\.php/);
	await expect(page.getByRole('heading', { name: /welcome/i })).toBeVisible();

	await page.goto(`${BASE}/company/profile.php`);
	await expect(page.getByRole('heading', { name: /profil entreprise/i })).toBeVisible();

	await page.goto(`${BASE}/company/add_job.php`);
	await expect(page.getByRole('heading', { name: /créer une offre d'emploi/i })).toBeVisible();

	await page.goto(`${BASE}/company/my_jobs.php`);
	await expect(page.getByRole('heading', { name: /gérer les postes/i })).toBeVisible();

	await page.goto(`${BASE}/company/applicants.php`);
	await expect(page.getByRole('heading', { name: /candidatures reçues/i })).toBeVisible();
});
