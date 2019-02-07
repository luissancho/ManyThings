var id = 0, cur = -1, prv = -1;

$.fn.mtDatepicker = function(allowRange, onClose) {
	$(this).datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: 'c-80:c+1',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        closeText: 'Done',
        prevText: 'Previous',
        nextText: 'Next',
        currentText: 'Today',
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        monthNamesShort: ['Jan','Feb','Mar','Apr', 'May','Jun','Jul','Aug','Sep', 'Oct', 'Nov', 'Dec'],
        dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        dayNamesShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
        dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
        beforeShow: function(input, inst) {
            var v = $(this).val();
	        id = $(this).attr('id');

	        try {
	            if (v.indexOf(' - ') > -1) {
	                var d = v.split(' - ');

	                prv = $.datepicker.parseDate('dd/mm/yy', d[0]).getTime();
	                cur = $.datepicker.parseDate('dd/mm/yy', d[1]).getTime();
	            } else if (v.length > 0) {
	                prv = cur = $.datepicker.parseDate('dd/mm/yy', v).getTime();
	            }
	        } catch (e) {
	            cur = prv = -1;
	        }
        },
        beforeShowDay: function(date) {
            return [true, ((date.getTime() >= Math.min(prv, cur) && date.getTime() <= Math.max(prv, cur)) ? 'date-range-selected' : '')];
        },
        onSelect: function(dateStr, inst) {
            if (allowRange) {
	            prv = cur;
	            cur = (new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay)).getTime();
	            if (prv == -1 || prv == cur) {
	                prv = cur;
	                $(this).val(dateStr);
	            } else {
	                var d1, d2;
	                d1 = $.datepicker.formatDate('dd/mm/yy', new Date(Math.min(prv, cur)), {});
	                d2 = $.datepicker.formatDate('dd/mm/yy', new Date(Math.max(prv, cur)), {});
	                $(this).val(d1 + ' - ' + d2);
	            }

	            inst.inline = true;
	        } else {
	        	prv = cur;
	        	$(this).val(dateStr);
	        }
        },
        onClose: function(input, inst) {
        	inst.inline = false;

        	if ($.isFunction(onClose)) {
        		onClose();
        	}
        }
    });
}