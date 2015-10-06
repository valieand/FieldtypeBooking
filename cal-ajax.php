<?php

if ($config->ajax)
{
	//determine whodunnit
	$type = $input->post->type;
	$data = array();

	//grab and sanitize sent inputs
    $pID        = (int) $input->post->pageid;//the page being edited with our FieldtypeBooking
    $pFieldName = $sanitizer->fieldName($input->post->fieldname);//the name of the field of type FieldtypeBooking on this page's template
    $timeStamp  = (int) $input->post->timeslot;//the last selected timeslot
    $bookedUser = $sanitizer->text($input->post->user);//the last selected user
    $dateFormat = $sanitizer->text($input->post->dateFormat);//the last selected user

	//get the page with this fiedl
	$p = $pages->get($pID);

	//if we found the page, set new values for this field (overwriting older ones)
	if ($p->id > 0)
    {
		if ($type == 'book')
        {
			//set outputformatting off
			$p->of(false);

			//any of the below save methods will work

			//Method 1: creating a new Booking object
			/*$book = new Booking();
			$book->date = $timeStamp;
			$book->user = $bookedUser;
			$p->$pFieldName->date = $book->date;
			$p->$pFieldName->user = $book->user;*/

			//Method 2: using set($key, $value) method
			#$p->$pFieldName->set('date', $timeStamp);
			#$p->$pFieldName->set('user', $bookedUser);

			//Method 3: directly setting values
			$p->$pFieldName->date = $timeStamp;
			$p->$pFieldName->user = $bookedUser;

			//save only this field for our page
			$p->save($pFieldName);

			$renderFreshCalendar = $p->$pFieldName->renderCalendar($timeStamp, $bookedUser, $dateFormat);
			#$bookedUser = 'fail';//@@note: for testing error msg below and in InputfieldBooking.js

			if($p->$pFieldName->date == $timeStamp && $p->$pFieldName->user == $bookedUser)
            {
				$data['bookings'] = $renderFreshCalendar;
				$data['message'] = 'success';
			}

			else
            {
				$data['message'] = 'error';
			}

			$p->of(true);//set outputformatting back on we do it here to allow comparison of unformatted date == $timeStamp

		}//end if $type == 'book'

		//else select free users for this datetime slot
		elseif ($type == 'freeusers')
        {
			$options = '';
			$freeUsers = $p->$pFieldName->getUsers($timeStamp);
			//$freeUsers = array();//@@note: for testing fail

			if(count($freeUsers))
            {
				$options .= '<option value="default">Select a user</option>';

				//$k = timeslot, $v = user name. ideally, there should also be a user ID
				foreach ($freeUsers as $k => $v)
                {
					//ideally, value='xx' should be a user ID. OK for demo here though
					$options .= '<option value="' . $v . '">' . $v . '</option>';
				}

				$data['freeusers'] = $options;
				$data['message'] = 'success';
			}

			else
            {
				$data['message'] = 'error';
			}

		}//end if $type == 'timeslot [getUsers()]'

		//if we just want to refresh the calendar with latest data from the server
		elseif ($type == 'refresh')
        {
			$formattedDateTime = $p->$pFieldName->date;//this is a formatted datetime

			$date = DateTime::createFromFormat($dateFormat, $formattedDateTime)->setTimeZone(new DateTimeZone(date_default_timezone_get()));
			$timeStamp = $date->getTimestamp();

			$bookedUser = $p->$pFieldName->user;

			$renderFreshCalendar = $p->$pFieldName->renderCalendar($timeStamp , $bookedUser, $dateFormat);

			$data['refresh'] = $renderFreshCalendar;
			$data['message'] = 'success';
		}//end if $type == 'refresh'

	}//end if $p->id;

	echo json_encode($data);

	exit;

	return;
}

/*
else {

	$session->redirect($pages->get('/')->url);
	echo 'no ajax';
}*/
