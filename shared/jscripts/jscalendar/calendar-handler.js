var DateDisabled = {
	0 : [ 21, 25, 28, 29, 30 ],
	1 : [ 15, 16, 17, 19 ]
};

var CDate = {};
(function (date) {
	CDate = {
		   w: date.getDay(),
		   d: date.getDate(),
		   m: date.getMonth(),
		   y: date.getFullYear(),
		date: date,
	};
 })(new Date);

function dateStatus (date, y, m, d) {
	var diff = date - CDate.date;
	if (diff < 172800000 || diff > 5356800000) return true;

	if (DateDisabled && DateDisabled[m]) {
		for (var i in DateDisabled[m])
			if (DateDisabled[m][i] == d)
				return true;
	}

	return false;
};

window.onload = function () {
	Calendar.setup({
		inputField:  'f_date',
		ifFormat:    '%u, %e, %m, %Y',
		displayArea: 'f_date_show',
		daFormat:    '%A, %B %e, %Y',
		button:      'f_date_trigger',
		firstDay:    1,
		weekNumbers: false,
		range:       [CDate.y, CDate.y],
		dateStatusFunc: dateStatus
	});
};
