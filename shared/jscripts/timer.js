//============================================================+
// File name   : timer.js
// Begin       : 2004-04-29
// Last Update : 2026-06-22
//
// Description : display clock and countdown timer
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * Clock and exam countdown timer.
 *
 * The public entry point FJ_start_timer() is invoked from inline page scripts
 * (see shared/code/tce_page_timer.php and public/code/tce_test_execute.php),
 * so it is exposed on the global object together with the `enable_countdown`
 * flag, which those pages read to avoid starting the countdown twice.
 *
 * NOTE: this timer is for display/convenience only. The authoritative
 * test-time enforcement must always be performed server-side, as the client
 * clock can be altered by the user.
 */
(function (global) {
	'use strict';

	// --- internal state -------------------------------------------------------
	let enableCountdown = false; // countdown mode (true) or wall-clock mode (false)
	let remainingTime = 0;       // (server now - test end) in seconds at start; negative while time is left
	let msgEndtime = '';         // message shown when the time is over
	let startTime = 0;           // client epoch time (ms) when the timer was started
	let displayEndtime = true;   // whether the end-of-time popup still has to be shown
	let timeoutLogout = false;   // log the user out (true) or go to the index page (false) at the end
	let timeDiff = 0;            // server/client clock offset (ms), used by the wall clock only
	let intervalId = null;       // handle of the active periodic timer

	// Left-pad a number to (at least) two digits.
	function pad2(value) {
		return String(value).padStart(2, '0');
	}

	// Stop the periodic update, if running.
	function stopTimer() {
		if (intervalId !== null) {
			global.clearInterval(intervalId);
			intervalId = null;
		}
	}

	/**
	 * Display the current server date-time, or the remaining time (countdown),
	 * on the read-only "timer" input field.
	 */
	function updateTimer() {
		const timerField = global.document.getElementById('timer');

		if (enableCountdown) { // --- COUNTDOWN MODE ---
			// Time relative to the end of the test, in seconds: negative while
			// time is left, zero or positive once it is over.
			const diffSeconds = remainingTime + ((Date.now() - startTime) / 1000);
			let sign = '-';

			// Two seconds before the end, auto-submit the test form once to save
			// the current answer. The interval is stopped right away so this page
			// can no longer fire the end-of-time redirect below and abort the
			// in-flight submission; that redirect runs on the reloaded page
			// instead (where `finish` is already 1 and the time is up).
			const testform = global.document.getElementById('testform');
			if ((diffSeconds >= -2) && testform && testform.finish && Number(testform.finish.value) === 0) {
				testform.finish.value = 1;
				stopTimer();
				testform.submit();
				return;
			}

			if (diffSeconds >= 0) {
				sign = '+';
				if (displayEndtime && (msgEndtime.length > 1)) {
					displayEndtime = false;
					stopTimer();
					global.alert(msgEndtime);
					global.location.replace(timeoutLogout ? 'tce_logout.php' : 'index.php');
					return;
				}
			}

			// Split the absolute remaining time into HH:mm:ss.
			const absSeconds = Math.abs(diffSeconds);
			const hours = Math.floor(absSeconds / 3600);
			const minutes = Math.floor((absSeconds % 3600) / 60);
			const seconds = Math.floor(absSeconds % 60);
			if (timerField) {
				timerField.value = `${sign}${pad2(hours)}:${pad2(minutes)}:${pad2(seconds)} `;
			}
		} else { // --- CLOCK MODE ---
			const now = new Date(Date.now() + timeDiff);
			if (timerField) {
				timerField.value = `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`
					+ ` ${pad2(now.getHours())}:${pad2(now.getMinutes())}:${pad2(now.getSeconds())}`;
			}
		}
	}

	/**
	 * Start (or restart) the timer.
	 *
	 * @param {boolean} countdown    if true enable the countdown, otherwise show the wall clock
	 * @param {number}  remaining    (server now - test end) in seconds; negative while time is left
	 * @param {string}  msg          message to display at the end of the countdown
	 * @param {boolean} logout       if true log the user out at the end of the available time
	 * @param {number}  [servertime] server epoch time in ms; used to align the wall clock
	 */
	function startTimer(countdown, remaining, msg, logout, servertime) {
		startTime = Date.now();
		// Align the displayed wall clock with the server clock. The extra ~60 ms
		// roughly compensate for the request/render latency between the server
		// stamping `servertime` and this code running. Unused in countdown mode.
		timeDiff = (typeof servertime === 'number') ? (servertime - startTime + 60) : 0;
		enableCountdown = countdown;
		remainingTime = remaining;
		msgEndtime = msg;
		timeoutLogout = logout;
		// Expose the flag read by the inline page scripts.
		global.enable_countdown = countdown;

		// Start a single interval (replacing any previous one) and render at once
		// so the field is not blank for the first half second. updateTimer() may
		// call stopTimer() to terminate early, hence it runs after the interval
		// is assigned.
		stopTimer();
		intervalId = global.setInterval(updateTimer, 500);
		updateTimer();
	}

	// --- public API -----------------------------------------------------------
	global.enable_countdown = enableCountdown;
	global.FJ_start_timer = startTimer;
	global.FJ_timer = updateTimer; // kept for backward compatibility
})(window);
