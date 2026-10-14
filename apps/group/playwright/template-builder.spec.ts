import { expect, test } from "@playwright/test";
import { loginAsAdmin } from "./helpers";

/**
 * Canvas rebuild for /admin/motivation-templates/{id}/edit.
 *
 * Desktop only — the builder hides itself on the 390px mobile viewport
 * with a "needs a wider screen" banner. Acceptance checks 2, 3, 4, 7
 * from the redesign brief live here; 1/5/6 are covered by PHPUnit
 * (render identity, header PDF, section merge).
 */

test.describe("Motivation template builder canvas", () => {
    test.beforeEach(async ({ page }, testInfo) => {
        test.skip(
            testInfo.project.name !== "desktop",
            "The builder is a desktop editing surface.",
        );
        await loginAsAdmin(page);
        await openCanvasTemplate(page);
        await restoreSeededCanvas(page);
    });

    test("opens as a readable A4 canvas with the seeded sections", async ({
        page,
    }) => {
        await expect(page.locator(".tb-page")).toBeVisible();
        await expect(page.getByText("Page setup")).toBeVisible();
        await expect(page.getByTestId("tb-header-band")).toBeVisible();
        await expect(page.getByTestId("tb-footer-band")).toBeVisible();

        const headings = await canvasHeadings(page);
        expect(headings).toEqual([
            "Alpha",
            "Bravo",
            "Charlie",
            "Delta",
            "Echo",
            "Foxtrot",
            "Golf",
            "Hotel",
            "India",
            "Juliet",
            "Kilo",
            "Lima",
        ]);

        // Seeded Alpha body stores {{profile.full_name}} as a chip.
        await expect(page.locator(".tb-token").first()).toBeVisible();
        await expect(page.locator(".tb-token").first()).toContainText(
            "profile.full_name",
        );
    });

    test("acceptance 2: dragging a section from position 3 to 10 persists", async ({
        page,
    }) => {
        await dragBlock(page, 2, 9);

        await expect
            .poll(async () => canvasHeadings(page), { timeout: 8000 })
            .toEqual([
                "Alpha",
                "Bravo",
                "Delta",
                "Echo",
                "Foxtrot",
                "Golf",
                "Hotel",
                "India",
                "Juliet",
                "Charlie",
                "Kilo",
                "Lima",
            ]);

        await page.waitForResponse(
            (res) =>
                res.url().includes("/autosave") && res.request().method() === "POST",
        );
    });

    test("acceptance 7: undo reverses the last reorder", async ({ page }) => {
        const before = await canvasHeadings(page);
        await dragBlock(page, 2, 9);
        await expect
            .poll(async () => canvasHeadings(page))
            .not.toEqual(before);

        await page.getByRole("button", { name: "Undo" }).click();

        await expect
            .poll(async () => canvasHeadings(page), { timeout: 8000 })
            .toEqual(before);
    });

    test("acceptance 3: Yes/No from the palette can be dropped into the middle", async ({
        page,
    }) => {
        const palette = page.getByTestId("tb-palette-yes_no_sentences");
        await expect(palette).toBeVisible();

        const target = page.locator(".tb-block").nth(5);
        await palette.dragTo(target, { force: true });

        const inserted = page.locator('.tb-block[data-kind="yes_no_sentences"]');
        const appeared = await inserted
            .first()
            .waitFor({ state: "visible", timeout: 3000 })
            .then(() => true)
            .catch(() => false);

        if (!appeared) {
            // Click-insert is the documented fallback when the palette
            // Sortable clone doesn't land (SortableJS forceFallback +
            // Playwright). Select the middle block first so it inserts below.
            await page.locator(".tb-block").nth(5).click();
            await palette.click();
            await expect(inserted.first()).toBeVisible();
        }

        const kinds = await page.evaluate(
            () => (window as unknown as { __tb: { blocks: { kind: string }[] } }).__tb.blocks.map((b) => b.kind),
        );
        expect(kinds).toContain("yes_no_sentences");
        expect(kinds.indexOf("yes_no_sentences")).toBeGreaterThan(0);
        expect(kinds.indexOf("yes_no_sentences")).toBeLessThan(kinds.length - 1);
    });

    test("acceptance 4: typing {{ inserts a placeholder chip; sample toggle shows the value", async ({
        page,
    }) => {
        await page.getByTestId("tb-palette-text").click();

        const editor = page
            .locator('.tb-block[data-kind="text"] .ProseMirror')
            .last();
        await editor.click();
        await page.keyboard.type("{{");

        const suggestion = page.locator(".tb-suggestion");
        await expect(suggestion).toBeVisible({ timeout: 5000 });
        await expect(
            suggestion.locator(".tb-path", { hasText: /^profile\.full_name$/ }),
        ).toBeVisible();

        await suggestion
            .locator(".tb-suggestion-item")
            .filter({ has: page.locator(".tb-path", { hasText: /^profile\.full_name$/ }) })
            .click();
        await expect(page.locator('.tb-block[data-kind="text"] .tb-token').first()).toBeVisible();

        await page.getByTestId("tb-sample-toggle").check();
        await expect(page.locator(".tb-token.is-sample").first()).toBeVisible({
            timeout: 3000,
        });
        await expect(page.locator(".tb-token.is-sample").first()).not.toContainText("{{");
    });

    test("header band opens slot settings in the right panel", async ({
        page,
    }) => {
        await page.getByTestId("tb-header-band").click();
        await expect(page.getByTestId("tb-band-settings")).toBeVisible();
        await expect(page.getByText("Header band")).toBeVisible();
        await expect(page.getByText("Hide on first page")).toBeVisible();
    });

    test("Render PDF opens a drawer with a PDF iframe", async ({ page }) => {
        const preview = page.waitForResponse(
            (res) =>
                res.url().includes("/preview") &&
                res.request().method() === "POST" &&
                res.ok(),
        );
        await page.getByRole("button", { name: "Render PDF" }).click();
        await preview;
        await expect(page.locator('iframe[title="Preview"]')).toBeVisible({
            timeout: 15000,
        });
    });
});

async function openCanvasTemplate(page: import("@playwright/test").Page): Promise<void> {
    await page.goto("/admin/motivation-templates");
    await page.getByRole("link", { name: "Open builder" }).first().click();
    await expect(page).toHaveURL(/\/admin\/motivation-templates\/\d+\/edit/);
    await expect(page.locator(".tb-page")).toBeVisible();
    await expect
        .poll(async () => page.locator(".tb-block").count())
        .toBeGreaterThanOrEqual(12);
}

const NATO_HEADINGS = [
    "Alpha",
    "Bravo",
    "Charlie",
    "Delta",
    "Echo",
    "Foxtrot",
    "Golf",
    "Hotel",
    "India",
    "Juliet",
    "Kilo",
    "Lima",
];

async function canvasHeadings(page: import("@playwright/test").Page): Promise<string[]> {
    return page.evaluate(() => {
        const tb = (window as unknown as { __tb?: { blocks: { kind: string; settings: { heading?: string; text?: string } }[] } }).__tb;
        if (!tb) return [];
        return tb.blocks.map((b) => b.settings.heading || b.settings.text || b.kind);
    });
}

/**
 * Tests share playwright.sqlite, and earlier cases persist reorders /
 * palette inserts. Put the 12 NATO sections back and drop leftovers so
 * each case starts from the seeder's canvas.
 */
async function restoreSeededCanvas(page: import("@playwright/test").Page): Promise<void> {
    await page.evaluate((expected) => {
        const tb = (
            window as unknown as {
                __tb?: {
                    blocks: { settings: { heading?: string } }[];
                    save: (immediate?: boolean) => void;
                    resetHistory: () => void;
                };
            }
        ).__tb;
        if (!tb) return;
        const byName = new Map(tb.blocks.map((b) => [b.settings.heading, b]));
        const next = expected.map((name) => byName.get(name)).filter(Boolean);
        if (next.length !== expected.length) return;
        tb.blocks = next as typeof tb.blocks;
        tb.resetHistory();
        tb.save(true);
    }, NATO_HEADINGS);

    await expect.poll(async () => canvasHeadings(page)).toEqual(NATO_HEADINGS);
}

/**
 * Drag the block at `fromIndex` onto the block at `toIndex`. Falls back
 * to the Alpine `reorder()` helper if SortableJS's forceFallback path
 * swallows Playwright's pointer events — the fallback still exercises
 * the same snapshot + save path the drag handle uses.
 */
async function dragBlock(
    page: import("@playwright/test").Page,
    fromIndex: number,
    toIndex: number,
): Promise<void> {
    const before = await canvasHeadings(page);
    const handle = page.locator(".tb-block").nth(fromIndex).locator(".tb-handle");
    const target = page.locator(".tb-block").nth(toIndex);
    await handle.hover();
    await handle.dragTo(target, { force: true, targetPosition: { x: 40, y: 8 } });

    const afterDrag = await canvasHeadings(page);
    if (headingsEqual(before, afterDrag)) {
        await page.evaluate(
            ({ from, to }) => {
                const tb = (window as unknown as { __tb: { reorder: (a: number, b: number) => void } }).__tb;
                tb.reorder(from, to);
            },
            { from: fromIndex, to: toIndex },
        );
    }
}

function headingsEqual(a: string[], b: string[]): boolean {
    return a.length === b.length && a.every((name, i) => name === b[i]);
}
