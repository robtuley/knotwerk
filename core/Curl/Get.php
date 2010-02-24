<?php
/**
 * Defines the T_Curl_Get class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a CURL GET request.
 *
 * @package core
 */
class T_Curl_Get extends T_Curl_Request
{

    function __construct($url,$headers=array())
    {
        // prepare headers
        $h = array();
		foreach ($headers as $name => $val)
			if (strlen($val)>0) $h[] = $name.': '.$val;

        // build request
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        if (count($h)>0) curl_setopt($curl,CURLOPT_HTTPHEADER,$h);
        parent::__construct($curl);
    }

}