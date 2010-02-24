<?php
/**
 * Defines the T_Validate_Time class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Validates a freeform time input.
 *
 * This filter accepts:
 *  o True 24hr times '0120','2300'
 *  o Dot or colon delimited 24hr times '1:20','23:00'
 *  o 12hr times '1:20am','23:00pm'
 *  o 12hr times with missing minutes '2pm'
 *
 * @package forms
 */
class T_Validate_Time extends T_Filter_Skeleton
{

    /**
     * Validates a string as a time.
     *
     * @param string $value  data to filter
     * @return int  filtered value
     */
    protected function doTransform($value)
    {
        $value = mb_trim($value);
        $regex = '/^(\d\d?)[\.\:]?(\d\d)?\s*(am|pm)?$/i';
        $matches = null;
        if (!preg_match($regex,$value,$matches)) $this->triggerError();
        $hour = $matches[1];
        $min  = isset($matches[2]) ? $matches[2] : false;
		$am_or_pm = isset($matches[3]) ? $matches[3] : false;
		if ($min===false && $am_or_pm===false) $this->triggerError();
        $hour = (int) ltrim($hour,'0');
        if ($hour>23 || ($am_or_pm!==false && $hour>12)) $this->triggerError();
        $min = (int) ltrim($min,'0');
        if ($min>59) $this->triggerError();

        if (strcasecmp($am_or_pm,'pm')===0) {
            $hour += 12;
            if ($hour==24) $hour=12;
        } elseif (strcasecmp($am_or_pm,'am')===0 && $hour==12) {
            $hour = 0;
        }

        return $hour*60*60 + $min*60;
    }

    /**
     * Trigger an error.
     *
     * @throws T_Exception_Filter
     */
    protected function triggerError()
    {
        throw new T_Exception_Filter('is an invalid time: format is hh:mm (e.g. 2:30pm or 1430)');
    }

}