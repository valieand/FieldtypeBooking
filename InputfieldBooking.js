$(document).ready(function() {

	//@@TODO: File could do with some refactoring!

	//declare some globals
    timeslot   = '';
    user       = '';
    url        = '';
    pageID     = '';
    fieldName  = '';
    dateFormat = '';

	spinner = function(d) {
		if(d =='in') $("#booking_spinner").fadeIn();
		else $("#booking_spinner").fadeOut('slow');
	};

	/* configs from InputfieldBooking*/

	moduleAjaxConfig = config.InputfieldBooking;

	if(!jQuery.isEmptyObject(moduleAjaxConfig)) {
        url        = moduleAjaxConfig.config.ajaxURL;
        pageID     = moduleAjaxConfig.config.pageID;
        fieldName  = moduleAjaxConfig.config.fieldName;
        dateFormat = moduleAjaxConfig.config.dateFormat;
	}//end if moduleAjaxConfig not empty


	//AJAX: Get Users + Selected Timeslot

	//get selected user @@note => IDEALLY USE ID FOR VALUES INSTEAD IN <option>s!!!
	$(document).on('change', 'select#booking_free_users', function() {
		user = $(this).val();
	});

	//impt: using .on in order to be able to manipulate the inserted html
	$('div#booking_bookings').on('click', 'button.booking_free', function() {

		$('button.booking_free').removeClass('booking_selected');//remove last selected
		$(this).addClass('booking_selected');//add to latest selected
		timeslot = $(this).attr('data-timeslot');

		timeStamp = new Date(timeslot*1000);

		//on click ajax call to load available users

		//ajax call to save latest booked datetime slot to db.
		//@@note: need separate ajax call for saving to external source
		$.ajax({
			url: url,
			type: 'POST',
			data: {type:'freeusers', timeslot:timeslot, 'pageid':pageID, 'fieldname':fieldName},
			dataType: 'json',
			beforeSend: spinner(d='in'),
		})

		.done(function(data) {
			if(data.message == 'success') {
				//@@note: here we load users returned as <option><option> in our <select>
				$('select#booking_free_users').html(data.freeusers);//replace select's options
				spinner(d='out');
			}

			else alert('There was an error fetching current users');
		})

		.fail(function() {	alert( 'There was an error fetching current users' ); })
		//end ajax

		//ajax call to save latest booked datetime slot to external source
		/*$.ajax({
			url: 'your url',
			type: 'POST',
			data: {type:'freeusers', timeslot:timeslot, 'pageid':pageID, 'fieldname':fieldName},
		});*/
 
	});

	//AJAX: Book
	//impt: using .on in order to be able to manipulate the inserted html
	$('div#booking_bookings').on('click', 'button#booking_book', function(){

        if(timeslot && user) {

			//ajax call to save latest booked datetime slot to db. Note: need separate ajax call for saving to external source
			$.ajax({
				url: url,
				type: 'POST',
				data: {type:'book', timeslot:timeslot, 'user':user, 'pageid':pageID, 'fieldname':fieldName, dateFormat:dateFormat},
				dataType: 'json',
				beforeSend: spinner('in'),
			})

			.done(function(data) {
				if(data.message == 'success') {
					$('div#booking_bookings').hide();
					$('div#booking_bookings').html(data.bookings);
					$('div#booking_bookings').fadeIn('slow');
					timeslot = '';//empty/reset the timeslot
					user = '';//empty/reset the timeslot
					spinner('out');
				}

				else alert('There was an error saving your booking');
			})

			.fail(function() {	alert( 'There was an error saving your booking' ); })

			//ajax call to book and save at your external source
			/*$.ajax({
				url: 'your url',
				type: 'POST',
				data: {type:'book', timeslot:timeslot, 'user':user, 'pageid':pageID, 'fieldname':fieldName},

			});*/

        }//end if timeslot

        else alert('You must select a timeslot and a user first!');

	});

	//AJAX: Refresh
	$('div#booking_bookings').on('click', 'button#booking_refresh', function() {

		$('button.booking_free').removeClass('booking_selected');//remove last selected

		//ajax call to save latest booked datetime slot to db. Note: need separate ajax call for saving to external source
		$.ajax({
			url: url,
			type: 'POST',
			data: {type:'refresh', 'pageid':pageID, 'fieldname':fieldName, dateFormat:dateFormat},
			dataType: 'json',
			beforeSend: spinner('in'),

		})

		.done(function(data) {
			if(data.message == 'success') {
				$('div#booking_bookings').hide();
				$('div#booking_bookings').html(data.refresh);
				$('div#booking_bookings').fadeIn('slow');
				timeslot = '';//empty/reset the timeslot
				spinner('out');
			}

			else alert('There was an error fetching bookings');
		})

		.fail(function() {	alert( 'There was an error fetching bookings' ); })
		//end ajax

		//ajax call to save latest booked datetime slot to external source
		/*$.ajax({
			url: 'your url',
			type: 'POST',
			data: {type:'refresh', 'pageid':pageID, 'fieldname':fieldName},

			}
		});*/

	});
});
