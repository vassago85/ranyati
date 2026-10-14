import type { Page } from "@playwright/test";
import { expect } from "@playwright/test";

/**
 * Admin credentials seeded by `database/seeders/PlaywrightSeeder.php`.
 * Changing either side of this pair in isolation will produce "Invalid
 * credentials" failures across the whole spec, so keep them wired to
 * the seeder's ADMIN_EMAIL / ADMIN_PASSWORD constants.
 */
export const ADMIN_EMAIL = "playwright-admin@ranyati.test";
export const ADMIN_PASSWORD = "playwright-admin-pass";

/**
 * Logs in as the seeded admin and lands on `/admin/choose` (the area
 * chooser is where fresh admins without a saved default_admin_area
 * end up after login).
 */
export async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto("/admin/login");
    await page
        .getByLabel(/email/i)
        .first()
        .fill(ADMIN_EMAIL);
    await page.getByLabel(/password/i).fill(ADMIN_PASSWORD);
    await Promise.all([
        page.waitForURL(/\/admin(\/|$)/),
        page.getByRole("button", { name: /sign in|log in/i }).click(),
    ]);
    // After login the admin may land on /admin/choose (no saved area
    // yet) or /admin (if one is already saved). The next test step
    // always navigates explicitly, so we just assert we're not still
    // on the login page.
    await expect(page).not.toHaveURL(/\/admin\/login/);
}

/**
 * Returns the real pixel width of `document.documentElement` so a
 * test can assert that no fix produced a horizontal page scrollbar
 * at a given viewport width.
 */
export async function pageScrollWidth(page: Page): Promise<number> {
    return await page.evaluate(() => document.documentElement.scrollWidth);
}
