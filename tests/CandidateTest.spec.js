const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE || 'http://localhost/Projet_Stage';
const PASSWORD = 'TestPass123!';

test.describe('Candidate Section', () => {
  test('dashboard candidat', async ({ page }) => {
    const email = `cand_dashboard_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate Dashboard');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.goto(`${BASE}/candidate/dashboard.php`);
    await expect(page.getByRole('heading', { name: 'Notifications' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Mes Candidatures' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Mon Profil' })).toBeVisible();
  });

  test('mes candidatures', async ({ page }) => {
    const email = `cand_applications_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate Applications');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.goto(`${BASE}/candidate/applications.php`);
    await expect(page.getByRole('heading', { name: 'Mes Candidatures' })).toBeVisible();
    await expect(page.getByText("Suivez l'état de vos postulations.")).toBeVisible();
  });

  test('profil candidat', async ({ page }) => {
    const email = `cand_profile_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate Profile');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.goto(`${BASE}/candidate/profile.php`);
    await expect(page.locator('input[name="full_name"]')).toBeVisible();
    await expect(page.locator('input[type="email"]')).toBeDisabled();
    await expect(page.locator('textarea[name="skills"]')).toBeVisible();
  });

  test('mise a jour profil', async ({ page }) => {
    const email = `cand_update_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate Update');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.goto(`${BASE}/candidate/profile.php`);
    await page.locator('input[name="full_name"]').fill('Candidate Updated');
    await page.locator('input[name="phone"]').fill('0612345678');
    await page.locator('input[name="country"]').fill('Maroc');
    await page.locator('textarea[name="address"]').fill('Casablanca, Maroc');
    await page.locator('textarea[name="skills"]').fill('PHP, JavaScript, Playwright');
    await page.locator('button[type="submit"]').click();

    await expect(page.getByText('Profil mis à jour avec succès')).toBeVisible();
  });

  test('liste des offres', async ({ page }) => {
    const email = `cand_jobs_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate Jobs');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.goto(`${BASE}/jobs/index.php`);
    await expect(page.locator('#search')).toBeVisible();
    await expect(page.locator('body')).toContainText('Sofyane Jobs');
  });

  test('count notifications api', async ({ page }) => {
    const email = `cand_api_${Date.now()}@test.local`;

    await page.goto(`${BASE}/Public/Register.php`);
    await page.locator('input[name="fullname"]').fill('Candidate API');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('select[name="role"]').selectOption('candidate');
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('input[name="confirm_password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(PASSWORD);
    await page.locator('button[type="submit"]').click();

    const response = await page.request.get(`${BASE}/candidate/ajax_unread_count.php`);
    expect(response.ok()).toBeTruthy();
    const payload = await response.json();
    expect(payload).toHaveProperty('count');
  });
});