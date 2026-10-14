import { defineConfig, devices } from "@playwright/test";
import path from "node:path";
import { fileURLToPath } from "node:url";

// package.json sets "type": "module", so neither __dirname nor
// require.resolve exist in this file. Rebuild them from import.meta.url
// so the config stays portable across Node LTS releases.
const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Playwright config for the Ranyati admin UI regression suite.
 *
 * Why a dedicated sqlite DB + `php artisan serve`:
 *
 *  - The dev database (`database/database.sqlite`) is the admin's own
 *    working data on Laragon. We never want browser-driven tests to
 *    overwrite that, so we point the webServer at
 *    `database/playwright.sqlite`, which `global-setup.ts` recreates
 *    from scratch on every run.
 *
 *  - Running against `artisan serve` on a dedicated port isolates us
 *    from whatever vhost Laragon has mapped, and lets the suite run
 *    the same way in CI if we ever port it there.
 *
 *  - Only Chromium is installed on dev machines, so we only run the
 *    desktop + mobile Chromium projects. Adding Firefox/WebKit later
 *    is a one-line change.
 */
const PORT = 8765;
const BASE_URL = `http://127.0.0.1:${PORT}`;

// Keep the webServer's DB override and the global-setup's DB_DATABASE
// identical — a mismatch here will produce ghost "no admin fixture"
// failures where the seeder wrote to one file and the app read another.
const PLAYWRIGHT_SQLITE = path.resolve(
    __dirname,
    "database",
    "playwright.sqlite",
);

export default defineConfig({
    testDir: "./playwright",
    testMatch: /.*\.spec\.ts$/,
    fullyParallel: false, // single sqlite file — don't trample ourselves
    workers: 1,
    retries: 0,
    reporter: [["list"], ["html", { outputFolder: "playwright-report", open: "never" }]],
    globalSetup: path.resolve(__dirname, "playwright/global-setup.ts"),
    use: {
        baseURL: BASE_URL,
        trace: "retain-on-failure",
        screenshot: "only-on-failure",
        video: "off",
        actionTimeout: 10_000,
        navigationTimeout: 15_000,
    },
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${PORT}`,
        url: BASE_URL + "/admin/login",
        reuseExistingServer: !process.env.CI,
        timeout: 30_000,
        stdout: "pipe",
        stderr: "pipe",
        env: {
            APP_ENV: "testing",
            APP_DEBUG: "true",
            APP_URL: BASE_URL,
            DB_CONNECTION: "sqlite",
            DB_DATABASE: PLAYWRIGHT_SQLITE,
            CACHE_STORE: "array",
            SESSION_DRIVER: "file",
            MAIL_MAILER: "array",
            QUEUE_CONNECTION: "sync",
            // Suppress the "APP_KEY not set" boot error — the real
            // dev .env already has a key, but artisan serve inherits
            // these env vars with higher precedence.
            APP_KEY: "base64:S0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0U=",
        },
    },
    projects: [
        {
            name: "desktop",
            use: {
                ...devices["Desktop Chrome"],
                viewport: { width: 1536, height: 900 },
            },
        },
        {
            name: "mobile",
            use: {
                ...devices["Pixel 7"],
                // Pin to the width Phase 1 bugs 4 & 5 were called out
                // against (390 × 844 — iPhone 14 / Pixel 7).
                viewport: { width: 390, height: 844 },
            },
        },
    ],
});
