<?php

namespace donatj;

/**
 * Simple Calendar
 *
 * @author Jesse G. Donat <donatj@gmail.com>
 * @link http://donatstudios.com
 * @license http://opensource.org/licenses/mit-license.php
 *
 */
class EventCalendar {

    private $now = false;
    private $daily_html = array();

    /**
     * Array of Day Event - Style
     * @var array
     * KAOM Vibolrith
     * 05.06.2013
     */
    private $daily_css = array();
    private $offset = 0;

    /**
     * Array of Week Day Names
     *
     * @var array
     */
    public $wday_names = false;

    /**
     * Constructor - Calls the setDate function
     *
     * @see setDate
     * @param null|string $date_string
     * @return EventCalendar
     */
    function __construct($date_string = null) {

        require_once setUserLoacalization();
        require_once 'include/Common.inc.php';

        $this->setDate($date_string);
    }

    /**
     * Sets the date for the calendar
     *
     * @param null|string $date_string Date string parsed by strtotime for the calendar date. If null set to current timestamp.
     */
    public function setDate($date_string = null) {
        if ($date_string) {
            $this->now = getdate(strtotime($date_string));
        } else {
            $this->now = getdate();
        }
    }

    /**
     * Add a daily event to the calendar
     *
     * @param string      $html The raw HTML to place on the calendar for this event
     * @param string      $start_date_string Date string for when the event starts
     * @param bool|string $end_date_string Date string for when the event ends. Defaults to start date
     * @return void
     */
    public function addDailyHtml($html, $start_date_string, $end_date_string = false, $css = false) {

        static $htmlCount = 0;
        $start_date = strtotime($start_date_string);
        if ($end_date_string) {
            $end_date = strtotime($end_date_string);
        } else {
            $end_date = $start_date;
        }

        $working_date = $start_date;
        do {
            $tDate = getdate($working_date);
            $working_date += 86400;
            $this->daily_html[$tDate['year']][$tDate['mon']][$tDate['mday']][$htmlCount] = $html;
            $this->daily_css[$tDate['year']][$tDate['mon']][$tDate['mday']][$htmlCount] = $css;
        } while ($working_date < $end_date + 1);

        $htmlCount++;
    }

    /**
     * Clear all daily events for the calendar
     *
     * @return void
     */
    public function clearDailyHtml() {
        $this->daily_html = array();
    }

    private function array_rotate(&$data, $steps) {
        $count = count($data);
        if ($steps < 0) {
            $steps = $count + $steps;
        }
        $steps = $steps % $count;
        for ($i = 0; $i < $steps; $i++) {
            array_push($data, array_shift($data));
        }
    }

    /**
     * Sets the first day of Week
     * 
     * @param int|string $offet Day to start on, ex: "Monday" or 0-6 where 0 is Sunday
     */
    public function setStartOfWeek($offet) {
        if (is_int($offet)) {
            $this->offset = $offet % 7;
        } else {
            $this->offset = date('N', strtotime($offet)) % 7;
        }
    }

    /**
     * Show the Calendars current date
     *
     * @param bool $echo Whether to echo resulting calendar
     * @return string
     */
    public function show($echo = true) {

        if ($this->wday_names) {
            $wdays = $this->wday_names;
        } else {
            $today = (86400 * (date("N")));
            for ($i = 0; $i < 7; $i++) {
                $wdays[] = strftime('%a', time() - $today + ($i * 86400));
            }
        }

        $this->array_rotate($wdays, $this->offset);
        $wday = date('N', mktime(0, 0, 1, $this->now['mon'], 1, $this->now['year'])) - $this->offset;
        $no_days = cal_days_in_month(CAL_GREGORIAN, $this->now['mon'], $this->now['year']);

        $out = '<table cellpadding="0" cellspacing="0" class="EventCalendar"><thead><tr>';

        /**
         * KAOM Vibolrith 
         * 05.06.2013
         */
        for ($i = 0; $i < 7; $i++) {

            switch ($wdays[$i]) {
                case "Sun":
                    $NAME_OF_DAY = SUNDAY;
                    break;
                case "Mon":
                    $NAME_OF_DAY = MONDAY;
                    break;
                case "Tue":
                    $NAME_OF_DAY = TUESDAY;
                    break;
                case "Wed":
                    $NAME_OF_DAY = WEDNESDAY;
                    break;
                case "Thu":
                    $NAME_OF_DAY = THURSDAY;
                    break;
                case "Fri":
                    $NAME_OF_DAY = FRIDAY;
                    break;
                case "Sat":
                    $NAME_OF_DAY = SATURDAY;
                    break;
            }

            $out .= '<th>' . $NAME_OF_DAY . '</th>';
        }

        $out .= "</tr></thead>\n<tbody>\n<tr>";

        if ($wday == 7) {
            $wday = 0;
        } else {
            $out .= str_repeat('<td class="SCprefix">&nbsp;</td>', $wday);
        }

        $count = $wday + 1;
        for ($i = 1; $i <= $no_days; $i++) {
            $out .= '<td' . ($i == $this->now['mday'] && $this->now['mon'] == date('n') ? ' class="today"' : '') . '>';

            $datetime = mktime(0, 0, 1, $this->now['mon'], $i, $this->now['year']);

            $out .= '<time datetime="' . date('Y-m-d', $datetime) . '">' . $i . '</time>';

            /**
             * KAOM Vibolrith
             * 05.06.2013
             */
            $HTML_ARRAY = false;
            $BGCOLOR_ARRAY = false;

            if (isset($this->daily_html[$this->now['year']][$this->now['mon']][$i])) {
                $HTML_ARRAY = $this->daily_html[$this->now['year']][$this->now['mon']][$i];
            }

            if (isset($this->daily_css[$this->now['year']][$this->now['mon']][$i])) {
                $BGCOLOR_ARRAY = $this->daily_css[$this->now['year']][$this->now['mon']][$i];
            }

            if (is_array($HTML_ARRAY)) {
                foreach ($HTML_ARRAY as $index => $HTML_CONTENT) {

                    if (isset($BGCOLOR_ARRAY[$index])) {
                        $out .= '<div style="background: ' . $BGCOLOR_ARRAY[$index] . ';color:' . getFontColor($BGCOLOR_ARRAY[$index]) . ';padding: 5px;line-height: 1.5em;border-bottom: 1px solid #99bbe8;">' . $HTML_CONTENT . '</div>';
                    } else {
                        $out .= '<div class="event">' . $HTML_CONTENT . '</div>';
                    }
                }
            }

            $out .= "</td>";

            if ($count > 6) {
                $out .= "</tr>\n" . ($i != $count ? '<tr>' : '');
                $count = 0;
            }
            $count++;
        }
        $out .= ($count != 1 ? str_repeat('<td class="SCsuffix">&nbsp;</td>', 8 - $count) : '') . "</tr>\n</tbody></table>\n";
        if ($echo) {
            echo $out;
        }

        return $out;
    }

}