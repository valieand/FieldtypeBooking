<?php

/**
 * Helper WireData Class to hold a Booking object
 *
 */
class Booking extends WireData
{
    /**
     * Construct a new Booking
     *
     */
    public function __construct()
    {
        //define the fields/properties that represent our Booking's items
        $this->set('date', ''); //last saved datetime/timeslot
        $this->set('user', ''); //last saved user for above datetime
        $this->set('calendar', array()); //
        $this->set('users', array());
    }

    /**
     * Set a value to the booking: date and user
     *
     */
    public function set($key, $value)
    {
        //we don't need to do much here + our values are already sanitized in sanitizeValue()
        return parent::set($key, $value);
    }

    /**
     * Retrieve a value from the booking: date and user
     *
     */
    public function get($key)
    {
        //we don't need to do much here + our values are already sanitized/formated for ouput in formatValue()
        return parent::get($key);
    }

    /**
     * Return an array of users available for a particular timeslot
     * Needs the requested timeslot passed in as a parameter (timestamp)?
     *
     * @access public
     * @return array
     *
     */
    public function getUsers($timeSlot = null)
    {
        //############### - DUMMY DATA!!! - ############

        //@@note: this dummy data is not synchronised with the getCalendar() generated data below; both these are randomly generated!

        $freeUsers = array(); //free users for various timeslots dummy data
        $allUsers  = array();

        if(!$timeSlot) return $freeUsers; //if no timeslot given no need to proceed

        for ($i = 1; $i < 31; $i++) {//generate 30 dummy users for dev/demo
            $allUsers[] = 'User' . $i;
        }

        $date = new DateTime('today');//The time is set to 00:00:00 today
        $date->setTime(9,0);//set our start time, 09:00

        //this will generate the array Array  ($timestamp => $user)
        //dummy data for free users for various timeslots for dev purposes

        //table rows (timeslots 0900-2100)
        for ($t = 9; $t < 22; $t = $t + 2)
        {
            //table columns (dates today + next 13 days)
            for ($d = 0 ; $d < 14; $d++) {

                //unix timestamp of (all) timeslots/datetime
                $timestamp = $date->getTimestamp();

                //@@note: When picking only one entry, array_rand() returns the key for a random entry.
                $freeUsers[$timestamp] = array_rand(array_flip($allUsers), mt_rand(1,15));//@@note: random availability for dev/testing only
                $date->modify('+1 day');//forward 1 day
            }

            //refresh the start time for the next row. The time is set to 00:00:00 today
            $date = new DateTime('today');
            $date->setTime($t+2,0);//set our next time

        } //end for $t

        $currentFreeUsers = array();

        //@@note: for dev purposes; we filter out the CURRENTLY FREE USERS
        foreach ($freeUsers as $key => $value)
        {
            //forward lookup - return three digit numeric mask
            if($timeSlot === $key)
            {
                if(!is_array($value)) $currentFreeUsers[] = $value;
                else $currentFreeUsers = $value;
                break;
            }
        }

        return $currentFreeUsers;
    }

    /**
     * Return an array of date and time slots available for the next 2 weeks starting from today
     * Calendar date always starts from today
     * There are 6 time slots, each lasting 2 hours each, starting at 9am and finishing at 9pm
     *
     * @access public
     * @return array
     *
     */
    public function getCalendar()
    {
        //############### - DUMMY DATA!!! - ############

        /*
            - array of bookings (unavailable timeslots)
            - these should be unix timestamps
            - the timeslots are predefined in 7 timeslots (9,11,13,15,17,19,21) lasting 2 hours each
            - the first date is always 'today' and the first hour always '9am'. That's the starting point
        */

        $calendar = array();

        $date = new DateTime('today');//The time is set to 00:00:00 today
        $date2 = new DateTime('today');//The time is set to 00:00:00 today
        $date->setTime(9,0);//set our start time
        $date2->setTime(9,0);//set our start time

        $j = 0;//holds our timeslots (0-6) [9am-9pm]

        /*
            this will generate the array

                Array $timeslot array => (
                                        $time => $date
                                    )
        */
        //table rows (timeslots 0900-2100)
        for ($t = 9; $t < 22; $t = $t + 2)
        {
            #echo '<hr>timeslots are: ' . $date->format('d m Y H: i') . '<br>';

            //table columns (dates today + next 13 days)
            for ($d = 0 ; $d < 14; $d++)
            {
                //unix timestamp of (all) timeslots/datetime
                $timestamp = $date2->getTimestamp();

                $calendar[$j][$timestamp] = mt_rand(0,1);//@@note: random availability for dev/testing only
                $date2->modify('+1 day');//forward 1 day

                #if($d==0) echo '<hr>';
                #echo $date2->format('d m Y H: i') . '<br>';
            }

            $j++;

            //refresh the start time for the next row. The time is set to 00:00:00 today
            #$date = new DateTime('today');
            $date->modify('+2 hours');//forward 1 day
            $date2 = new DateTime('today');
            #$date->setTime($t+2,0);//set our next time
            $date2->setTime($t+2,0);//set our next time

        }//end for $t

        return $calendar;
    }

    /**
     * Return a 2-week tabular calendar showing bookings for predefined timeslots
     * Calendar date always starts from today
     * There are 6 time slots, each lasting 2 hours each, starting at 9am and finishing at 9pm
     *
     * @param int $lastDateTime The last saved datetimeslot (timstamp)
     * @param string $lastUser The last user booked
     * @param string|int|array $dateFormat PHP Date Time format
     * @access public
     * @return string
     *
     */
    public function renderCalendar($lastDateTime = null, $lastUser = null, $dateFormat)
    {
        //@@note: in case our $dateFormat is empty, we assume the FieldtypeBooking default 'd F Y H:i'
        $dateFormat = $dateFormat ? $dateFormat : 'd F Y H:i';

        $calendar = $this->getCalendar();

        $i      = 0;
        $tbody  = ''; //for timeslots rows
        $thcols = ''; //for timeslots table column headers
        $cnt    = count($calendar[0]); //will use this for our <th> output cnt;
        $date   = new DateTime('today');

        foreach ($calendar as $slot => $daytimes)
        {
            $tbody .= '<tr>';

            //$dt = timestamp; $a = availability
            foreach ($daytimes as $dt => $a)
            {
                $date->setTimestamp($dt);
                $startDay  = $date->format('D');
                $startDate = $date->format('j/n');

                if($i < $cnt) $thcols .= "<th class='booking_header'>" . $startDay . '<br>' . $startDate . "</th>";
                $ts = $date->format('G') . '<sup class="booking_hour">' . $date->format('i') . '</sup>';//visible timeslot on datetimes buttons

                //CSS class to apply to unavailable/booked timeslots/datetimes
                if($lastDateTime == $dt) $booked = 'booking_last_saved';//$lastDateTime is a timestamp
                elseif (1 == $a) $booked = 'booking_booked';//if $a is true, this slot is booked
                else $booked = 'booking_free';

                $tbody .= "
                    <td>
                        <button type='button' class='" . $booked . "' name='timeslot' data-timeslot='" . $dt . "'>" . $ts ."</button>
                    </td>
                ";
                $i++;
            }

            $tbody .= "</tr>";

        }//end foreach $calendar

        if($lastDateTime)
        {
            $dt = new DateTime();
            $dt->setTimestamp($lastDateTime);
            $lastDateTimeFormatted = $dt->format($dateFormat);
        }

        //else nothing booked yet
        else $lastDateTimeFormatted = $this->_('No time booked');

        $lastUser = $lastUser ? $lastUser : $this->_('No user booked');

        $refreshButton = "<button type='button' id='booking_refresh' value='Refresh' name='refresh'>Refresh</button>";
        $bookButton    = "<button type='button' id='booking_book' name='book' value='Book' name='book'>Book</button>";

        //final booking table for output
        $out = "
            $refreshButton $bookButton <span id='booking_spinner'>
            <i class='fa fa-lg fa-spin fa-cog'></i></span>
            <table class='booking_table'>
                <thead>
                    <tr>
                        $thcols
                    </tr>
                </thead>
                <tbody>
                    $tbody
                </tbody>
            </table>
        ";

        //users panel
        $defaultUsersTxt = $this->_('Select timeslot to load users');

        $out .= "
            <div id='booking_users_panel'>
                <div id='booking_users_wrapper'>
                    <select id='booking_free_users'>
                        <option value='default'>$defaultUsersTxt</option>
                    </select>
                </div>
                <div id='booking_last_wrapper'>
                    <span id='booking_last'>Booked Date-Time: $lastDateTimeFormatted<br>
                    Booked User: $lastUser</span>
                </div>
            </div>
        ";

        return $out;
    }

    /**
     * Return a properly formatted date
     *
     * @param int|string $date The date to convert (if an integer)
     * @param string|int|array $dateFormat PHP Date Time format
     * @access public
     * @return string
     *
     */
    public function convertDate($date, $dateFormat)
    {
        //in case our $dateFormat is empty, we assume the Fieldtype default 'd F Y H:i'
        $dateFormat = $dateFormat ? $dateFormat : 'd F Y H:i';

        //if we get a unix timestamp, format that as a date string  and set correct timezone
        //if it is a string, we assume it is our properly formatted date (see formatValue())
        //if we get nothing, no date has been booked yet

        if (is_int($date) && $date)
        {
            $dt = DateTime::createFromFormat('U', $date)->setTimeZone(new DateTimeZone(date_default_timezone_get()));
            #$date = $dt->format('l j F Y H:i');
            $date = $dt->format($dateFormat);
        }

        //default message if no booking date found
        elseif(!$date) $date = $this->_('No booking found');
        #else $value->date = '';

        return $date;
    }

    /**
     * Provide a default rendering for a booking/calendar for the current page
     * This is used if field is echo'ed, e.g. echo $page->booking;
     *
     * @access public
     * @return string
     *
     */
    public function renderBooked()
    {
        //remember page's output formatting state
        $of = $this->page->of();
        //turn on output formatting for our rendering (if it's not already on)
        if(!$of) wire('page')->of(true);
        $out = "<p><strong>Booked Date Time: $this->date</strong><br>Booked User: $this->user</p>";
        if(!$of) $this->page->of(false);

        return $out;
    }

    /**
     * Return a string representing this booking
     * This is used if field is echo'ed, e.g. echo $page->booking;
     *
     * @access public
     * @return string
     *
     */
    public function __toString()
    {
        return $this->renderBooked();
    }

}
