/**
 * Thin wrapper around SortableJS that returns a `destroy` function. Used
 * by the Alpine canvas directive to re-bind sortable when the DOM changes.
 */
import Sortable from 'sortablejs';

export function bindSortable(el, options = {}) {
    if (!el || el.dataset.tbSortable === '1') return () => {};
    el.dataset.tbSortable = '1';

    const instance = Sortable.create(el, {
        animation: 150,
        handle: options.handle || '.tb-handle',
        draggable: options.draggable || '.tb-sortable-item',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        forceFallback: true, // use the JS fallback so our styles apply reliably
        fallbackOnBody: true,
        swapThreshold: 0.6,
        group: options.group,
        onEnd: options.onEnd,
        onAdd: options.onAdd,
        onUpdate: options.onUpdate,
    });

    return () => {
        instance.destroy();
        delete el.dataset.tbSortable;
    };
}
