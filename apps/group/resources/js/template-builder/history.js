/**
 * A small undo/redo stack for the template builder. Snapshots are shallow
 * JSON clones of `{blocks, header, footer}`. We only touch three keys so
 * cloning stays cheap even on big templates.
 *
 * Usage:
 *   const h = createHistory({ blocks, header, footer });
 *   // Before a mutation that's user-visible:
 *   h.push({ blocks, header, footer });
 *   // After:
 *   const state = h.undo();   // or h.redo()
 */
export function createHistory(initial, limit = 100) {
    let stack = [snapshot(initial)];
    let cursor = 0;

    function snapshot(state) {
        return JSON.stringify({
            blocks: state.blocks ?? [],
            header: state.header ?? {},
            footer: state.footer ?? {},
        });
    }

    function parse(entry) {
        return JSON.parse(entry);
    }

    return {
        /**
         * Record the state AFTER a user-visible mutation. Undo walks
         * back to the previous entry (the initial load, or the last
         * committed edit). Identical pushes are ignored so a no-op
         * drag does not create an empty undo step.
         */
        push(state) {
            const snap = snapshot(state);
            if (snap === stack[cursor]) return;
            // Drop any redo entries beyond the cursor — a fresh edit after
            // an undo starts a new timeline.
            stack = stack.slice(0, cursor + 1);
            stack.push(snap);
            cursor = stack.length - 1;
            if (stack.length > limit) {
                stack.shift();
                cursor--;
            }
        },

        /**
         * Replace the top-of-stack entry without changing the cursor.
         * Used when a text edit "commits" after the autosave debounce.
         */
        replaceTop(state) {
            stack[cursor] = snapshot(state);
        },

        undo() {
            if (cursor === 0) return null;
            cursor--;
            return parse(stack[cursor]);
        },

        redo() {
            if (cursor >= stack.length - 1) return null;
            cursor++;
            return parse(stack[cursor]);
        },

        canUndo() { return cursor > 0; },
        canRedo() { return cursor < stack.length - 1; },

        reset(state) {
            stack = [snapshot(state)];
            cursor = 0;
        },
    };
}
