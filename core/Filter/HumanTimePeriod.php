<?php
/**
 * Contains the T_Filter_HumanTimePeriod class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Convert number of seconds to a human time period.
 *
 * e.g. 1 second, 34 seconds, 4 minutes, 2 hours 10 minutes, 4 hours,
 *      3 days, 3 days 5 hours.
 *
 * @package core
 */
class T_Filter_HumanTimePeriod extends T_Filter_Skeleton
{

    /**
     * Converts integer seconds to string.
     *
     * @param int $value  integer
     * @return string  filtered value
     */
    protected function doTransform($value)
    {
        if ($value<60) {
            return $value.' second'.($value==1 ? '' : 's');
        } elseif ($value<3570) { // 60*60-30 (rounded *up* when over half minute)
            $min = round($value/60,0);
            return $min.' minute'.($min==1 ? '' : 's');
        } elseif ($value<86370) { // 24*60*60-30 (rounded *up* when over half minute left to a day)
            $hrs  = floor($value/3600);
            $mins = round(($value-$hrs*3600)/60,0);
            if ($mins==60) { // e.g. 10:59:59
                ++$hrs;
                $mins = 0;
            }
            $human = $hrs.' hour'.($hrs==1 ? '' : 's');
            if ($mins>0) {
                $human .= ' '.$mins.' minute'.($mins==1 ? '' : 's');
            }
            return $human;
        } else { // 1 day or over
            $days = floor($value/86400);
            $hrs = round(($value-$days*86400)/3600,0);
            if ($hrs==24) { // e.g. 2 days 23:59:59
                ++$days;
                $hrs = 0;
            }
            $human = $days.' day'.($days==1 ? '' : 's');
            if ($hrs>0) {
                $human .= ' '.$hrs.' hour'.($hrs==1 ? '' : 's');
            }
            return $human;
        }
    }

}