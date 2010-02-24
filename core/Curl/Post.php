<?php
/**
 * Contains T_Curl_Post interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a CURL POST request.
 *
 * @package core
 */
class T_Curl_Post extends T_Curl_Request
{

    function __construct($url,$content,$headers=array())
    {
        // prepare headers
        $h = array();
		foreach ($headers as $name => $val)
			if (strlen($val)>0) $h[] = $name.': '.$val;

        // if content is array, parse to string
        if (is_array($content)) {
            $content = http_build_query($content,null,'&');
        }

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$content);
        if (count($h)>0) curl_setopt($curl,CURLOPT_HTTPHEADER,$h);
        parent::__construct($curl);
    }

}