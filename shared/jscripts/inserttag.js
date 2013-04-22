//============================================================+
// File name   : inserttag.js
// Begin       : 2001-10-25
// Last Update : 2012-12-29
//
// Description : Insert TAGS on Textarea Form (XHTML)
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * save the text history for undo/redo functions
 */
var text_history = new Array();

/**
 * current text history index
 */
var thid = 0;

/**
 * max text history index
 */
var maxthid = 0;

/**
 * current selection (for IE only)
 */
var txtsel = null;

/**
 * selection start
 */
var posStart;

/**
 * selection end
 */
var posEnd;

/**
 * Creates open and close tags and call display tag.
 * Use '&' as first tag character to obtain also a closed tag.
 * @param editText string text to be edited
 * @param tag string element to be added
 */
function FJ_insert_tag(editText, tag) {
	var opentag = tag;
	var closetag = '';
	if (opentag.length <= 0) {
		return;
	}
	tmpstr = opentag.split(' ');
	if (tag.charAt(opentag.length-2) != '/') {
		if (opentag.charAt(0) == '<') {
			//XHTML tag
			var closetag = '</'+tmpstr[0].substring(1,(tmpstr[0].length));
			if (closetag.charAt(closetag.length-1)!='>') {
				closetag += '>';
			}
		} else {
			//custom tag
			tmpstr = tmpstr[0].split('=');
			var closetag = '[/'+tmpstr[0].substring(1,(tmpstr[0].length));
			if (closetag.charAt(closetag.length-1)!=']') {
				closetag += ']';
			}
		}
	}
	FJ_display_tag(editText, opentag, closetag);
	return;
}

/**
 * Insert text before selected text or at the end of text.
 * @param editText string text to be edited
 * @param newtext string text to be added
 */
function FJ_insert_text(editText, newtext) {
	FJ_display_tag(editText, newtext, '');
	return;
}

/**
 * Insert open and close TAG on selected text.
 * @param editText string text to be edited
 * @param opentag string opening element to be added
 * @param closetag string closing element to be added
 */
function FJ_display_tag(editText, opentag, closetag) {
	// save previous text on history
	text_history[thid] = editText.value;
	thid++;
	if (editText.createTextRange && document.selection) { // if text has been selected (only IE browser)
		if (txtsel != null) {
			// uses always the last selection...
			txtsel = txtsel.duplicate();
			var sellen = 0; // selection length
			if (txtsel.text.length > 0) {
				sellen = txtsel.text.length + opentag.length + closetag.length;
				txtsel.text = opentag + '' + txtsel.text + '' + closetag;
			} else {
				editText.value = editText.value + '' + opentag + '' + closetag;
			}

			// restore selection
			txtsel.moveStart("character", - sellen);
			txtsel.select();
		} else {
			editText.value = editText.value + '' + opentag + '' + closetag;
		}
	} else if (window.getSelection && editText.setSelectionRange) { // MOZ
		posStart = editText.selectionStart;
		posEnd = editText.selectionEnd;
		editText.value = editText.value.substr(0, posStart) + '' + opentag + '' + editText.value.substr(posStart, posEnd-posStart) + '' + closetag + '' + editText.value.substr(posEnd);
		// renew selection range
		editText.setSelectionRange(posStart, (opentag.length * 2 + 1 + posEnd));
	} else { //text has not been selected or incompatible browser
		editText.value = editText.value + '' + opentag + '' + closetag;
	}
	// save current text on history
	text_history[thid] = editText.value;
	maxthid = thid;
	editText.focus();
	return;
}

/**
 * UNDO
 * Restore the text previous to last tag insert.
 * @since 3.0.008 (2006-05-13)
 * @param editText string text to be edited
 */
function FJ_undo(editText) {
	// undo
	if (thid > 0) {
		thid--;
		editText.value = text_history[thid];
	}
	return;
}

/**
 * REDO
 * Redoe the last tag insert.
 * @since 3.0.008 (2006-05-13)
 * @param editText string text to be edited
 */
function FJ_redo(editText) {
	if (thid < maxthid) {
		thid++;
		editText.value = text_history[thid];
	}
	return;
}

/**
 * Tracks selection changes.
 * Preserve selection on IE
 * @since 3.0.008 (2006-05-13)
 * @param editText string text to be edited
 */
function FJ_update_selection(editText) {
	if (editText.createTextRange && document.selection) {
		txtsel = document.selection.createRange();
	}
	return;
}

// -------------------------------------------------------------------------
// END OF SCRIPT
// -------------------------------------------------------------------------
