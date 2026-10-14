/**
 * Motivation template builder bundle.
 *
 * Registers the `templateBuilder` Alpine component and all its helpers on
 * `window`, so the Blade view can keep its `x-data='templateBuilder(...)'`
 * declaration while the heavy lifting (TipTap, SortableJS, history) lives
 * in importable modules.
 */
import '../../css/template-builder.css';

import { createBuilder } from './builder.js';
import { createEditor, placeholdersToJSON } from './editor.js';
import { bindSortable } from './sortable.js';

window.templateBuilder = createBuilder;
window.tbCreateEditor = createEditor;
window.tbBindSortable = bindSortable;
window.tbPlaceholdersToJSON = placeholdersToJSON;
