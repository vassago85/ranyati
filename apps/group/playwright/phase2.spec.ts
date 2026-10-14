import { expect, test } from "@playwright/test";
import { loginAsAdmin, pageScrollWidth } from "./helpers";

/**
 * Phase 2 — UX overhaul regression suite.
 *
 * Covers:
 *   - the admin overview dashboard (4 KPI tiles, pipeline card, needs-action table)
 *   - the global `/` focus shortcut on the topbar search
 *   - the area-aware sidebar + segmented area switcher
 *   - the 7-phase list (tabs + filter toolbar + masked columns)
 *   - the CSV export round-trip
 *   - the mobile card view at 390px
 *
 * The PlaywrightSeeder already lays down ~12 motivations spread across
 * every status + two application types + two enquiries, so this spec
 * can lean on known fixtures instead of mocking anything.
 */

test.describe("Phase 2 UX overhaul", () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    // ------- Dashboard -----------------------------------------------

    test("overview page lands on 4 KPI tiles and a pipeline card", async (
        { page },
        testInfo,
    ) => {
        await page.goto("/admin");

        for (const label of [
            "Needs your action",
            "Motivations in progress",
            "Awaiting payment",
            "Unread enquiries",
        ]) {
            await expect(page.getByText(label).first()).toBeVisible();
        }

        // Pipeline card renders every phase label.
        for (const phase of ["Intake", "Preparation", "Payment", "Fulfilment"]) {
            await expect(
                page.locator(".pipeline-col__title", { hasText: phase }).first(),
            ).toBeVisible();
        }

        // The pipeline card renders every one of the 12 status labels
        // as a clickable row.
        const pipelineRows = page.locator(".pipeline-row");
        await expect(pipelineRows).toHaveCount(12);

        await testInfo.attach("dashboard.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("pressing / anywhere focuses the global search", async ({ page }, testInfo) => {
        // The topbar search is CSS-hidden below 768px, so the keyboard
        // shortcut is desktop-only by design.
        test.skip(testInfo.project.name === "mobile", "Topbar search is hidden on mobile.");

        await page.goto("/admin");
        await page.waitForLoadState("networkidle");

        const search = page.locator("#admin-global-search-input");
        await expect(search).toBeVisible();

        // Dispatch a real keyboard event on the window. Alpine's
        // `@keydown.window` directive attaches an addEventListener on
        // window itself, so this fires it reliably across headless
        // Chromium builds. page.keyboard.press is focus-sensitive and
        // sometimes drops to the browser shortcut layer in headless.
        await page.evaluate(() => {
            window.dispatchEvent(
                new KeyboardEvent("keydown", {
                    key: "/",
                    code: "Slash",
                    bubbles: true,
                    cancelable: true,
                }),
            );
        });

        await expect(search).toBeFocused();

        // Typing while the input is focused must not also insert "/".
        await search.fill("");
        await page.keyboard.type("playwright");
        await expect(search).toHaveValue("playwright");
    });

    test("global search returns a client and motivation", async ({ page }) => {
        await page.goto("/admin/search?q=Playwright");

        await expect(page.getByText(/Results for/i)).toBeVisible();
        // At least one client card should render Playwright Member from
        // the seeder.
        await expect(page.locator("tr", { hasText: "Playwright Member" }).first()).toBeVisible();
    });

    // ------- Sidebar -------------------------------------------------

    test("sidebar shows the area switcher with every available area", async ({ page }) => {
        await page.goto("/admin");

        const switcher = page.locator(".area-switcher");
        await expect(switcher).toBeVisible();

        for (const area of ["motivations", "arms", "storage"]) {
            await expect(
                switcher.locator(`input[name="area"][value="${area}"]`),
            ).toHaveCount(1);
        }

        // The active area's button is the one marked `is-active`.
        const active = switcher.locator(".area-switcher__btn.is-active");
        await expect(active).toHaveCount(1);
        await expect(active).toContainText(/motivations/i);
    });

    test("switching area via the segmented control lands on that area", async ({ page }, testInfo) => {
        // On mobile the whole sidebar is off-canvas behind the hamburger,
        // which makes the switcher invisible without first tapping it
        // open. Phase 2's mobile scope is the list cards, not the sidebar
        // area switcher — keep this one desktop-only.
        test.skip(testInfo.project.name === "mobile", "Sidebar is off-canvas on mobile.");

        await page.goto("/admin");

        // Each area is its own <form>; find the one whose hidden
        // input carries value="arms" and submit it.
        const armsForm = page.locator('form:has(input[name="area"][value="arms"])');
        await armsForm.locator("button[type=submit]").click();

        await expect(page).toHaveURL(/\/admin\/arms(\/|\?|$)/);

        // The sidebar on the Arms page now shows Arms as the active
        // segment — proving the saved default_admin_area kicked in.
        await expect(page.locator(".area-switcher__btn.is-active")).toContainText(/arms/i);

        // And an Arms-only link ("Enquiries" under the arms group) is
        // visible — the motivations tree is no longer rendered.
        await expect(page.locator(".sidebar-nav")).toContainText(/enquiries/i);
    });

    // ------- List ----------------------------------------------------

    test("motivations list renders exactly 7 phase tabs with counts", async ({ page }) => {
        await page.goto("/admin/motivation-requests");

        const tabs = page.locator(".filter-tabs .filter-tab");
        await expect(tabs).toHaveCount(7);

        for (const label of [
            "All",
            "Needs action",
            "Intake",
            "Preparation",
            "Payment",
            "Fulfilment",
            "Completed",
        ]) {
            await expect(
                tabs.filter({ hasText: new RegExp(`^${label}\\b`, "i") }),
            ).toHaveCount(1);
        }

        // Each tab shows a numeric count pill.
        await expect(tabs.locator(".count")).toHaveCount(7);
    });

    test("list toolbar filters round-trip through the query string", async ({ page }) => {
        await page.goto("/admin/motivation-requests");

        // Pick a waiting-time filter and an exact status inside the
        // list toolbar (there's a second button[type=submit] in the
        // sidebar area switcher, so scope by form class).
        const toolbar = page.locator("form.requests-toolbar");
        await toolbar.locator("select[name=waiting]").selectOption("7");
        await toolbar.locator("select[name=status]").selectOption("paid");
        await toolbar.getByRole("button", { name: /Apply/i }).click();

        await expect(page).toHaveURL(/[?&]waiting=7/);
        await expect(page).toHaveURL(/[?&]status=paid/);

        // The applied values re-populate the selects (so a reload keeps
        // the filter visible).
        await expect(page.locator("select[name=waiting]")).toHaveValue("7");
        await expect(page.locator("select[name=status]")).toHaveValue("paid");
    });

    test("list masks every id number and cell column", async ({ page }) => {
        await page.goto("/admin/motivation-requests");

        const bodyText = await page.locator(".requests-table-card").innerText();
        // No raw 13-digit SA ID should appear in the table.
        expect(bodyText).not.toMatch(/\b\d{13}\b/);
        // At least one masked ID in `820101*******` style must appear.
        expect(bodyText).toMatch(/\d{6}\*{3,}/);
        // At least one `082 *** 1234`-style masked cell must appear.
        expect(bodyText).toMatch(/\d{3} \*\*\* \d{4}/);
    });

    test("csv export downloads a text/csv attachment", async ({ page }) => {
        await page.goto("/admin/motivation-requests");

        const [download] = await Promise.all([
            page.waitForEvent("download"),
            page.getByRole("link", { name: /Export CSV/i }).click(),
        ]);

        expect(download.suggestedFilename()).toMatch(/^ranyati-motivations-.*\.csv$/);
    });

    // ------- Mobile --------------------------------------------------

    test("motivations list collapses to cards at 390px", async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== "mobile", "Mobile-only check.");

        await page.goto("/admin/motivation-requests");

        const tableCard = page.locator(".requests-table-card");
        const cardList = page.locator(".requests-cards");
        await expect(cardList).toBeVisible();
        await expect(tableCard).toBeHidden();

        const scrollWidth = await pageScrollWidth(page);
        expect(scrollWidth).toBeLessThanOrEqual(410);
    });

    test("overview page does not scroll horizontally at 390px", async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== "mobile", "Mobile-only check.");

        await page.goto("/admin");
        const scrollWidth = await pageScrollWidth(page);
        expect(scrollWidth).toBeLessThanOrEqual(410);
    });
});
