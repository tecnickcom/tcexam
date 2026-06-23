//============================================================+
// File name   : inserttag.js
// Begin       : 2001-10-25
// Last Update : 2026-06-22
//
// Description : Insert TAGS on Textarea Form (XHTML)
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

'use strict';

(function (global) {

    /**
     * Text history stack used by the undo/redo functions.
     */
    const text_history = [];

    /**
     * Current text history index.
     */
    let thid = 0;

    /**
     * Highest reachable text history index.
     */
    let maxthid = 0;

    /**
     * Creates open and close tags and calls display tag.
     * Self-closing tags (e.g. <br/>) get no closing tag.
     * @param editText textarea element to be edited
     * @param tag string element to be added
     */
    function FJ_insert_tag(editText, tag) {
        const opentag = tag;
        if (opentag.length <= 0) {
            return;
        }
        let closetag = '';
        if (opentag.charAt(opentag.length - 2) !== '/') {
            if (opentag.charAt(0) === '<') {
                // XHTML tag
                const name = opentag.split(' ')[0];
                closetag = '</' + name.substring(1);
                if (closetag.charAt(closetag.length - 1) !== '>') {
                    closetag += '>';
                }
            } else {
                // custom (TCExam code) tag
                const name = opentag.split(' ')[0].split('=')[0];
                closetag = '[/' + name.substring(1);
                if (closetag.charAt(closetag.length - 1) !== ']') {
                    closetag += ']';
                }
            }
        }
        FJ_display_tag(editText, opentag, closetag);
    }

    /**
     * Insert text at the current cursor position or around the selection.
     * @param editText textarea element to be edited
     * @param newtext string text to be added
     */
    function FJ_insert_text(editText, newtext) {
        FJ_display_tag(editText, newtext, '');
    }

    /**
     * Insert open and close TAG around the selected text (or at the cursor).
     * @param editText textarea element to be edited
     * @param opentag string opening element to be added
     * @param closetag string closing element to be added
     */
    function FJ_display_tag(editText, opentag, closetag) {
        // save the current text on the history stack (state before the edit)
        text_history[thid] = editText.value;
        thid++;
        const posStart = editText.selectionStart;
        const posEnd = editText.selectionEnd;
        editText.value = editText.value.slice(0, posStart) + opentag
            + editText.value.slice(posStart, posEnd) + closetag
            + editText.value.slice(posEnd);
        // re-select the inserted region (open tag, original text and close tag)
        editText.setSelectionRange(posStart, posEnd + opentag.length + closetag.length);
        // save the new text on the history stack (state after the edit)
        text_history[thid] = editText.value;
        maxthid = thid;
        editText.focus();
    }

    /**
     * UNDO
     * Restore the text previous to the last tag insert.
     * @since 3.0.008 (2006-05-13)
     * @param editText textarea element to be edited
     */
    function FJ_undo(editText) {
        if (thid > 0) {
            thid--;
            editText.value = text_history[thid];
        }
    }

    /**
     * REDO
     * Redo the last tag insert.
     * @since 3.0.008 (2006-05-13)
     * @param editText textarea element to be edited
     */
    function FJ_redo(editText) {
        if (thid < maxthid) {
            thid++;
            editText.value = text_history[thid];
        }
    }

    // expose the public API for the inline event handlers
    global.FJ_insert_tag = FJ_insert_tag;
    global.FJ_insert_text = FJ_insert_text;
    global.FJ_display_tag = FJ_display_tag;
    global.FJ_undo = FJ_undo;
    global.FJ_redo = FJ_redo;

})(window);
