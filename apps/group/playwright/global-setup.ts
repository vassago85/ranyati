import { spawnSync } from "node:child_process";
import { existsSync, writeFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

// ESM shim — the repo's package.json is "type": "module".
const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Provisions a fresh sqlite database for the Playwright suite.
 *
 * We deliberately do NOT delete the sqlite file: on Windows the
 * `php artisan serve` process launched by Playwright's `webServer`
 * block starts in parallel with this global-setup, which locks the
 * file as soon as the first HTTP request hits a service provider
 * that touches the DB. Trying to `rmSync` under that lock fails with
 * EPERM.
 *
 * Instead we rely on `migrate:fresh --force` to drop every table and
 * re-run the migrations in place. The file (and any open sqlite
 * connections against it) survive the schema reset, and the next
 * request handled by the dev server will see the new tables.
 *
 * After migrating we invoke the dedicated `PlaywrightSeeder` to put
 * the admin, member, application type, verified motivation request,
 * enquiry and short-reference tracker application in place.
 *
 * The webServer config in `playwright.config.ts` points `DB_DATABASE`
 * at this same file, so by the time the spec starts hitting URLs the
 * fixtures the tests rely on are already in the DB.
 */
async function globalSetup(): Promise<void> {
    const projectRoot = path.resolve(__dirname, "..");
    const sqlitePath = path.join(projectRoot, "database", "playwright.sqlite");

    // sqlite requires the file to exist before `migrate` can open a
    // connection. Create an empty one if it is missing (first run or
    // the user deleted it manually between runs).
    if (!existsSync(sqlitePath)) {
        writeFileSync(sqlitePath, "");
    }

    const artisanEnv: NodeJS.ProcessEnv = {
        ...process.env,
        APP_ENV: "testing",
        DB_CONNECTION: "sqlite",
        DB_DATABASE: sqlitePath,
        CACHE_STORE: "array",
        SESSION_DRIVER: "file",
        MAIL_MAILER: "array",
        QUEUE_CONNECTION: "sync",
        APP_KEY: "base64:S0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0VZS0U=",
    };

    // `migrate:fresh` drops every table and reruns the full migration
    // tree. Equivalent to delete-the-file + migrate, but safe to run
    // while the dev server holds a connection to the file.
    runArtisan(["migrate:fresh", "--force"], projectRoot, artisanEnv);
    runArtisan(
        ["db:seed", "--force", "--class=Database\\Seeders\\PlaywrightSeeder"],
        projectRoot,
        artisanEnv,
    );
}

function runArtisan(
    args: string[],
    cwd: string,
    env: NodeJS.ProcessEnv,
): void {
    const label = `php artisan ${args.join(" ")}`;
    const result = spawnSync("php", ["artisan", ...args], {
        cwd,
        env,
        stdio: "pipe",
        encoding: "utf8",
        // Windows needs shell: true so the `php` on PATH resolves the
        // same way it does from an interactive PowerShell prompt.
        shell: process.platform === "win32",
    });

    if (result.status !== 0) {
        const stdout = result.stdout?.toString() ?? "";
        const stderr = result.stderr?.toString() ?? "";
        throw new Error(
            `Playwright global-setup: \`${label}\` exited ${result.status}\n` +
                `stdout:\n${stdout}\nstderr:\n${stderr}`,
        );
    }
}

export default globalSetup;
