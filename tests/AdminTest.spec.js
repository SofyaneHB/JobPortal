const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const mysql = require('mysql2/promise');

const BASE = 'http://localhost/Projet_Stage';
const ADMIN_EMAIL = 'playwright_admin@test.com';
const ADMIN_PASSWORD = 'AdminPass123!';

let connection;

test.beforeAll(async () => {
	const passwordHash = execFileSync('php', ['-r', 'echo password_hash($argv[1], PASSWORD_DEFAULT);', ADMIN_PASSWORD], {
		encoding: 'utf8'
	}).trim();

	connection = await mysql.createConnection({
		host: 'localhost',
		user: 'root',
		password: '',
		database: 'JobPortal'
	});

	await connection.execute('DELETE FROM users WHERE email = ?', [ADMIN_EMAIL]);
	await connection.execute(
		'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)',
		['Playwright Admin', ADMIN_EMAIL, passwordHash, 'admin']
	);
});

test.afterAll(async () => {
	if (connection) {
		await connection.execute('DELETE FROM users WHERE email = ?', [ADMIN_EMAIL]);
		await connection.end();
	}
});

async function loginAsAdmin(page) {
	await page.goto(`${BASE}/Public/login.php`);
	await page.locator('input[name="email"]').fill(ADMIN_EMAIL);
	await page.locator('input[name="password"]').fill(ADMIN_PASSWORD);
	await page.locator('button[type="submit"]').click();
	await expect(page).toHaveURL(/admin\/dashboard\.php/);
}

test.describe('Admin section', () => {
	test('admin dashboard page', async ({ page }) => {
		await loginAsAdmin(page);
		await expect(page.getByRole('heading', { name: /welcome back, admin/i })).toBeVisible();
		await expect(page).toHaveURL(/admin\/dashboard\.php/);
	});

	test('admin users page', async ({ page }) => {
		await loginAsAdmin(page);
		await page.goto(`${BASE}/admin/users.php`);
		await expect(page).toHaveURL(/admin\/users\.php/);
		await expect(page.getByRole('heading', { name: /manage users/i })).toBeVisible();
	});

	test('admin companies page', async ({ page }) => {
		await loginAsAdmin(page);
		await page.goto(`${BASE}/admin/companies.php`);
		await expect(page).toHaveURL(/admin\/companies\.php/);
		await expect(page.getByRole('heading', { name: /manage companies/i })).toBeVisible();
	});

	test('admin jobs page', async ({ page }) => {
		await loginAsAdmin(page);
		await page.goto(`${BASE}/admin/jobs.php`);
		await expect(page).toHaveURL(/admin\/jobs\.php/);
		await expect(page.getByRole('heading', { name: /manage jobs/i })).toBeVisible();
	});
});
