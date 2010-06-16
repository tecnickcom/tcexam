//============================================================+
// File name   : timer.js
// Begin       : 2004-04-29
// Last Update : 2010-02-12
// 
// Description : display clock and countdown timer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License: 
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
var msg_endtime = ""; // message to display at the end of time
var start_time = 0; // client computer datetime
var displayendtime = true; // display popup message indicating the end of the time
var timeout_logout = false; // if true logout user at the end of available time

/**
 * Display current date-time and remaining time (countdown)
 * on a input text form field (timerform.timer)
 */
function FJ_timer() {
	var today = new Date();  

	var year = "" + today.getFullYear();
	var month = "" + (1 + today.getMonth());
	if(month.length < 2) {
		month = "0" + month;
	}
	var day = "" + today.getDate();
	if(day.length < 2) {
		day = "0" + day;
	}
	var hour = "" + today.getHours();
	if(hour.length < 2) {
		hour = "0" + hour;
	}
	var minute = "" + today.getMinutes();
	if(minute.length < 2) {
		minute = "0" + minute;
	}
	var second = "" + today.getSeconds();
	if(second.length < 2) {
		second = "0" + second;
	}
	// build clock string
	var clockstring = year + "-" + month + "-" + day+ " " + hour + ":" + minute + ":" + second;
	var countdownstring = "";
	
	if (enable_countdown) {
				
		// elapsed time in seconds
		var diff_seconds = remaining_time + ((today.getTime() / 1000) - start_time);
				
		//get sign
		var sign = "-";
		
		// submit form for the last time to save user input on textbox (if any)
		if ((diff_seconds >= -2) && (document.getElementById('testform').finish.value == 0)) {
			document.getElementById('testform').finish.value = 1;
			document.getElementById('testform').submit();
		}
		
		if (diff_seconds >= 0) {
			sign = "+";
			if (displayendtime && (msg_endtime.length > 1)) {
				displayendtime = false;
				alert(msg_endtime);
				if (timeout_logout) {
					// logout
					window.location.replace("tce_logout.php");
				} else {
					// redirect user to index page
					window.location.replace("index.php");
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
		
		// build time string
		countdownstring = "" + sign + "" + diff_hours + ":" + diff_minutes + ":" + diff_seconds + " ";
		
		//display string on form field
		document.getElementById('timerform').timer.value = countdownstring;
	} else {
		//display string on form field
		document.getElementById('timerform').timer.value = clockstring;
	}
	
	return;
}

/**
 * Starts the timer
 * @param countdown boolean if true enable countdown
 * @param remaining int remaining test time in seconds
 * @param msg string message to display at the end of countdown
 * @param logout boolean if true logout user at the end of available time
 */
function FJ_start_timer(countdown, remaining, msg, logout) {
	enable_countdown = countdown;
	remaining_time = remaining;
	msg_endtime = msg;
	timeout_logout = logout;
	var startdate = new Date();
	start_time = (startdate.getTime() / 1000); // local computer time
	// update clock each second
	setInterval('FJ_timer()', 500);
}

// --------------------------------------------------------------------------
//  END OF SCRIPT
// --------------------------------------------------------------------------
