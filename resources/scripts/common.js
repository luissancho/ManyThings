$(function() {
	if (legal) {
		if (!Cookies.get('cookies')) {
			setTimeout(function() {
				$('#cookies').slideDown(500);
			}, 500);
	    }
	    Cookies.set('cookies', 'accepted', {
	    	expires: 365,
	    	path: '/',
	    	domain: domain
	    });
	}
});