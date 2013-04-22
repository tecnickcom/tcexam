//============================================================+
// File name   : timer.js
// Begin       : 2004-04-29
// Last Update : 2010-10-05
//
// Description : display clock and countdown timer
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
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

// global variables

var enable_countdown = false;
var remaining_time = 0; // countdown duration
var msg_endtime = ''; // message to display at the end of time
var start_time = 0; // client computer datetime in milliseconds
var displayendtime = true; // display popup message indicating the end of the time
var timeout_logout = false; // if true logout user at the end of available time
var time_diff = 0; // time difference between server and client in milliseconds

/**
 * Display current server date-time and remaining time (countdown)
 * on a input text form field (timerform.timer)
 */
function FJ_timer() {
	if (enable_countdown) { // --- COUNTDOWN MODE ---
		// get local time
		var today = new Date();
		// elapsed time in seconds
		var diff_seconds = remaining_time + ((today.getTime() - start_time) / 1000);
		//get sign
		var sign = '-';
		// submit form for the last time to save user input on textbox (if any)
		if ((diff_seconds >= -2) && (document.getElementById('testform').finish.value == 0)) {
			document.getElementById('testform').finish.value = 1;
			document.getElementById('testform').submit();
		}
		if (diff_seconds >= 0) {
			sign = '+';
			if (displayendtime && (msg_endtime.length > 1)) {
				displayendtime = false;
				alert(msg_endtime);
				if (timeout_logout) {
					// logout
					window.location.replace('tce_logout.php');
				} else {
					// redirect user to index page
					window.location.replace('index.php');
				}
			}
		}
		diff_seconds = Math.abs(diff_seconds); // get absolute value
		// split seconds in HH:mm:ss
		var diff_hours = Math.floor(diff_seconds / 3600);
		diff_seconds  = diff_seconds % 3600;
		var diff_minutes = Math.floor(diff_seconds / 60);
		diff_seconds  = Math.floor(diff_seconds % 60);
		if(diff_hours < 10) {
			diff_hours = "0" + diff_hours;
		}
		if(diff_minutes < 10) {
			diff_minutes = "0" + diff_minutes;
		}
		if(diff_seconds < 10) {
			diff_seconds = "0" + diff_seconds;
		}
		// display countdown string on form field
		document.getElementById('timerform').timer.value = ''+sign+''+diff_hours+':'+diff_minutes+':'+diff_seconds+' ';
	} else { // --- CLOCK MODE ---
		var localtime = new Date();
		var today = new Date((localtime.getTime() + time_diff));
		var year = ''+today.getFullYear();
		var month = ''+(1 + today.getMonth());
		if(month.length < 2) {
			month = '0'+month;
		}
		var day = ''+today.getDate();
		if(day.length < 2) {
			day = '0'+day;
		}
		var hour = ''+today.getHours();
		if(hour.length < 2) {
			hour = '0'+hour;
		}
		var minute = ''+today.getMinutes();
		if(minute.length < 2) {
			minute = '0'+minute;
		}
		var second = ''+today.getSeconds();
		if(second.length < 2) {
			second = '0'+second;
		}
		// display clock string on form field
		document.getElementById('timerform').timer.value = ''+year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
	}
	return;
}

/**
 * Starts the timer
 * @param boolean countdown if true enable countdown
 * @param int remaining remaining test time in seconds
 * @param string msg  message to display at the end of countdown
 * @param boolean logout if true logout user at the end of available time
 * @param int servertime the server time in milliseconds
 */
function FJ_start_timer(countdown, remaining, msg, logout, servertime) {
	var startdate = new Date();
	start_time = startdate.getTime();
	time_diff = servertime - start_time + 60;
	enable_countdown = countdown;
	remaining_time = remaining;
	msg_endtime = msg;
	timeout_logout = logout;
	// update clock
	setInterval('FJ_timer()', 500);
}

// --------------------------------------------------------------------------
//  END OF SCRIPT
// --------------------------------------------------------------------------
