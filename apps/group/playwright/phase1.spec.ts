import { expect, test } from "@playwright/test";
import { loginAsAdmin, openFirstMotivation, pageScrollWidth } from "./helpers";

/**
 * Browser-level regression suite for the Phase 1 "bugs from the audit"
 * fixes. Each test is phrased in terms of what an admin would actually
 * observe in the running app — tableWidth > wrapperWidth, horizontal
 * page scroll at 390 px, the Generate↔Regenerate toggle, etc. —
 * rather than inspecting the exact HTML the way the PHPUnit suite
 * does. The two layers back each other up: PHPUnit catches Blade
 * regressions on CI without a browser, Playwright catches CSS/JS
 * regressions and layout breakage that no amount of substring
 * matching would notice.
 */

test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
});

test.describe("Phase 1 bug audit", () => {
    test("bug 1: enquiries table no longer clips the row action, and the view column is gone", async ({
        page,
    }, testInfo) => {
        await page.goto("/admin/enquiries");

        // Bug 1 was: the table sat inside a card with hidden overflow,
        // so when the table was wider than the card the rightmost
        // column ("View" / Reply affordance) was clipped off the right
        // edge. The fix was to wrap the table in `.table-wrap`, which
        // gives the overflow its own scroll container.
        const table = page.locator("table").first();
        await expect(table).toBeVisible();

        const wrap = page.locator(".table-wrap").first();
        await expect(wrap).toBeVisible();

        // Prove the wrap is actually an overflow:auto|scroll container
        // (not just a styling div we accidentally called the same
        // thing). This is the real guarantee that the Reply/action
        // column reaches the user when the table is wider than the
        // card.
        const overflowX = await wrap.evaluate(
            (node: Element) => getComputedStyle(node).overflowX,
        );
        expect(["auto", "scroll"]).toContain(overflowX);

        // Secondary guarantee: when the table *is* wider than its
        // wrap, the wrap must be scrollable to that width. Compare
        // scrollWidth to clientWidth — if scrollWidth is at least the
        // table's natural width, every column is reachable.
        const widths = await wrap.evaluate((node: Element) => {
            const el = node as HTMLElement;
            const inner = el.querySelector("table") as HTMLElement | null;
            return {
                clientWidth: el.clientWidth,
                scrollWidth: el.scrollWidth,
                tableWidth: inner ? inner.scrollWidth : 0,
            };
        });
        expect(widths.scrollWidth).toBeGreaterThanOrEqual(
            widths.tableWidth - 2,
        );

        // The dedicated "View" button column was removed — the whole
        // row is a link. Guard against the exact previous markup.
        const viewButtons = page.locator(
            "table >> role=link[name='View']",
        );
        await expect(viewButtons).toHaveCount(0);

        // Clicking the row opens the enquiry.
        const firstRow = page.locator("table tr.row-link").first();
        await expect(firstRow).toBeVisible();
        await firstRow.click();
        await expect(page).toHaveURL(/\/admin\/enquiries\/\d+/);

        await testInfo.attach("enquiries-after-fix.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("bug 2: show page labels the button Generate when nothing has been generated", async ({
        page,
    }, testInfo) => {
        // The seeder puts exactly one Verified-status motivation in
        // the DB with no letters and no PDF, so the first row of the
        // index is the one we want. Phase 2 renders the list as a
        // table on desktop and as a stack of cards on mobile, so we
        // use the helper that targets both.
        await page.goto("/admin/motivation-requests");
        await openFirstMotivation(page);
        await expect(page).toHaveURL(/\/admin\/motivation-requests\/\d+/);

        // With no book / letters, the Actions card says Generate — not
        // Regenerate. Template-mode requests use "motivations"; the
        // legacy book pipeline still says "book".
        const actionsCard = page
            .locator("div.card")
            .filter({ has: page.locator("h2:text('Actions')") });
        await expect(actionsCard).toBeVisible();
        await expect(
            actionsCard.getByRole("button", { name: /^Generate (book|motivations)$/ }),
        ).toBeVisible();
        await expect(actionsCard.getByRole("button", { name: /^Regenerate / })).toHaveCount(0);

        await testInfo.attach("show-generate-button.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("bug 3: SAPS tracker shows an Unbatched row for short-reference applications", async ({
        page,
    }, testInfo) => {
        await page.goto("/admin/applications");

        const batchesCard = page
            .locator("div.card")
            .filter({ has: page.locator("h2:text('Batches')") });
        await expect(batchesCard).toBeVisible();

        // The honest reconciliation row — the key piece bug 3 was
        // missing. The copy mentions the specific reason so an admin
        // reading it can decide what to do.
        await expect(batchesCard.getByText("Unbatched")).toBeVisible();
        await expect(batchesCard.getByText(/ref < 4 digits/i)).toBeVisible();

        // The stat card at the top shows 1 Application — and the
        // Batches card now accounts for that same 1 row (via the
        // Unbatched row) instead of silently dropping it.
        const applicationsStat = page
            .locator("div.card.stat-card")
            .filter({ has: page.locator("div.stat-label:text('Applications')") });
        await expect(applicationsStat.locator(".stat-value")).toHaveText("1");

        await testInfo.attach("saps-tracker-unbatched.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("bug 4: motivation requests list exposes a 7-phase tab bar + exact-status select", async ({
        page,
    }, testInfo) => {
        // Phase 2 collapses the old 13-status filter bar into 7 phase
        // tabs ("All", "Needs action", 4 phases, "Completed") plus a
        // searchable exact-status select in the toolbar. The bug 4
        // guarantee — no horizontal page scroll at 390px — is still
        // tested below in the mobile-specific describe block.
        await page.goto("/admin/motivation-requests");

        const tabs = page.locator(".filter-tabs .filter-tab");
        await expect(tabs).toHaveCount(7);

        // Each tab still shows a count pill, so admins can see the
        // workload at a glance without clicking through.
        await expect(tabs.locator(".count")).toHaveCount(7);

        // The exact-status select still covers every one of the 12
        // statuses, so Phase 2's phase-first UI did not drop any
        // filtering capability from Phase 1.
        const statusSelect = page.locator("select[name=status]");
        await expect(statusSelect).toBeVisible();
        const optionCount = await statusSelect.locator("option").count();
        // 12 statuses + 1 "Any status" placeholder.
        expect(optionCount).toBe(13);

        await testInfo.attach("requests-phase-tabs.png", {
            body: await page.screenshot({ fullPage: false }),
            contentType: "image/png",
        });
    });

    test("bug 5: detail page uses .grid-2 for the top row of cards", async ({
        page,
    }, testInfo) => {
        await page.goto("/admin/motivation-requests");
        await openFirstMotivation(page);
        await expect(page).toHaveURL(/\/admin\/motivation-requests\/\d+/);

        // The responsive helper class is applied.
        const grid = page.locator(".grid-2").first();
        await expect(grid).toBeVisible();

        // The repeated inline `grid-template-columns:1fr 1fr` on the
        // top wrapper is gone (there is still one inside the fulfil-
        // ment card at paid status, but the test fixture is at
        // verified so that card isn't rendered).
        const inlineGrids = page
            .locator('[style*="grid-template-columns:1fr 1fr"]')
            .filter({ hasNot: page.locator("form") });
        await expect(inlineGrids).toHaveCount(0);

        await testInfo.attach("show-grid-2.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("bug 6: documents section shows an empty-state sentence when no docs exist", async ({
        page,
    }, testInfo) => {
        await page.goto("/admin/motivation-requests");
        await openFirstMotivation(page);

        const documentsCard = page
            .locator("div.card")
            .filter({ has: page.locator("h2:text('Documents')") });
        await expect(documentsCard).toBeVisible();
        await expect(documentsCard.locator(".empty-state")).toBeVisible();
        await expect(documentsCard.getByText(/No documents uploaded yet/i)).toBeVisible();
        // The sentence links to the Edit page so admins have an
        // obvious next step.
        await expect(
            documentsCard.getByRole("link", {
                name: /Edit answers.*documents/i,
            }),
        ).toBeVisible();

        await testInfo.attach("documents-empty-state.png", {
            body: await documentsCard.screenshot(),
            contentType: "image/png",
        });
    });

    test("bug 7: motivation-members page renders exactly one h1", async ({ page }, testInfo) => {
        await page.goto("/admin/motivation-members");

        const h1s = page.locator("h1");
        await expect(h1s).toHaveCount(1);
        // The remaining h1 is the one in the sticky topbar.
        await expect(h1s.first()).toContainText(/Motivation members/i);

        // The primary action moved into @section('actions').
        await expect(
            page.getByRole("link", { name: /\+?\s*New walk-in member/i }),
        ).toBeVisible();

        await testInfo.attach("members-index.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });

    test("bug 8: admin search inputs expose an aria-label to screen readers", async ({
        page,
    }) => {
        // Enquiries.
        await page.goto("/admin/enquiries");
        await expect(
            page.getByLabel("Search enquiries", { exact: true }),
        ).toBeVisible();

        // Motivation members.
        await page.goto("/admin/motivation-members");
        await expect(
            page.getByLabel("Search motivation members", { exact: true }),
        ).toBeVisible();

        // Arms listings.
        await page.goto("/admin/arms");
        await expect(
            page.getByLabel("Search arms listings", { exact: true }),
        ).toBeVisible();

        // Arms enquiries.
        await page.goto("/admin/arms/enquiries");
        await expect(
            page.getByLabel("Search arms enquiries", { exact: true }),
        ).toBeVisible();
    });
});

test.describe("Phase 1 — mobile-specific layout at 390 px", () => {
    test.skip(
        ({ page }) => page.viewportSize()?.width !== 390,
        "Only meaningful on the mobile project (390 × 844).",
    );

    test("bugs 4 & 5: no horizontal page scroll on the requests list or detail page", async ({
        page,
    }, testInfo) => {
        // Requests list — before the fix the 13-tab bar pushed the
        // page body to ~1100 px wide at a 390 px viewport.
        await page.goto("/admin/motivation-requests");
        const requestsScroll = await pageScrollWidth(page);
        expect(requestsScroll).toBeLessThanOrEqual(410); // tolerate ~5% rounding
        await testInfo.attach("mobile-requests.png", {
            body: await page.screenshot({ fullPage: false }),
            contentType: "image/png",
        });

        // Detail page — the Member and Status cards previously sat
        // side-by-side at ~150 px each. The grid-2 helper collapses
        // them on viewports under 768 px.
        await page.goto("/admin/motivation-requests");
        await openFirstMotivation(page);
        const detailScroll = await pageScrollWidth(page);
        expect(detailScroll).toBeLessThanOrEqual(410);

        // Specifically: the cards are stacked, not side-by-side.
        const memberCard = page
            .locator("div.card")
            .filter({ has: page.locator("h2:text('Member')") });
        const statusCard = page
            .locator("div.card")
            .filter({ has: page.locator("h2:text('Status')") });
        const memberBox = await memberCard.boundingBox();
        const statusBox = await statusCard.boundingBox();
        expect(memberBox).not.toBeNull();
        expect(statusBox).not.toBeNull();
        if (memberBox && statusBox) {
            // Status card sits BELOW the member card (grid-2 collapsed
            // to a single column), so its top > member's bottom.
            expect(statusBox.y).toBeGreaterThan(memberBox.y + memberBox.height - 10);
        }

        await testInfo.attach("mobile-detail.png", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
        });
    });
});
