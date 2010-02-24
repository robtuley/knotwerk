<?php
/**
 * Defines the T_Validate_Url class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Convert a string to a URL object.
 *
 * This parses URLs as defined by RFC 2396. The only exclusion is that only the
 * schemes FTP, HTTP and HTTPS are allowed, and any hostname including user
 * info is disallowed.
 *
 * @see http://www.ietf.org/rfc/rfc2396.txt
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_Url extends T_Filter_Skeleton
{

    /**
     * Attempts to parse URL.
     *
     * @return array
     */
    protected function parseUrl($url)
    {
        $parsed = @parse_url($url);
        if ($parsed === false) {
            throw new T_Exception_Filter('is a malformed URL');
        }
        return $parsed;
    }

    /**
     * Whether a path or fragment section is valid.
     *
     * Test whether a segment is value by recoding it and comparing it back to
     * the original. It guarentees a *safe* result, and checks the original was
     * OK.
     *
     * @param string $segment  path or fragment segment
     * @return bool whether segment validates
     */
    protected function isValidUrlEncoded($segment)
    {
        $unencoded = urldecode($segment);  /* allow spaces */
        $raw = rawurlencode($unencoded);
        $normal = urlencode($unencoded);
        return strcmp($raw,$segment)==0 || strcmp($normal,$segment)==0;
    }

    /**
     * Converts a string to a URL.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        /* try to parse URL using PHP */
        $parsed = $this->parseUrl($value);
        /* get scheme */
        if (isset($parsed['scheme'])) {
            $scheme = strtolower($parsed['scheme']);
            if (!in_array($scheme,array('http','ftp','https'))) {
                throw new T_Exception_Filter('uses an invalid scheme (http, https and ftp are permitted)');
            }
        } else {
            /* default scheme of 'http'. If there is no scheme in original,
               need to reparse the value with scheme added. */
            $scheme = 'http';
            $parsed = $this->parseUrl('http://'.$value);
        }
        /* get domain */
        if (!isset($parsed['host'])) {
            throw new T_Exception_Filter('is missing the hostname');
        }
        $host = strtolower($parsed['host']);
        $regex = '@^(?:[a-z\d-]+\.)+[a-z]{2,6}$@';
        if (!preg_match($regex,$host)) {
            throw new T_Exception_Filter('contains an invalid hostname');
        }
        $host = isset($parsed['port']) ? $host.':'.$parsed['port'] : $host;
        if (isset($parsed['username'])) {
            $msg = 'contains a username which is not permitted';
            throw new T_Exception_Filter($msg);
        }
        /* parse path */
        if (isset($parsed['path'])) {
            $path = explode('/',trim($parsed['path'],'/'));
            for ($i=0,$count=count($path); $i<$count; $i++) {
                if (strlen($path[$i])==0) {
                    unset($path[$i]);
                } elseif (!$this->isValidUrlEncoded($path[$i])) {
                    $msg = 'contains invalid character(s) in path';
                    throw new T_Exception_Filter($msg);
                } else {
                    $path[$i] = urldecode($path[$i]);
                }
            }
        } else {
            $path = array();
        }
        /* parse query string */
        if (isset($parsed['query'])) {
            parse_str($parsed['query'],$parameters);
            /* parse_str works likes mechanism to populate $_GET, so need to
               account for magic quotes */
            if (get_magic_quotes_gpc()) {
                $f = new T_Filter_NoMagicQuotes();
                $parameters = _transform($parameters,$f);
            }
        } else {
            $parameters = array();
        }
        /* parse fragment */
        if (isset($parsed['fragment'])) {
            $fragment = $parsed['fragment'];
            if (!$this->isValidUrlEncoded($fragment)) {
                throw new T_Exception_Filter('contains an invalid anchor');
            }
        } else {
            $fragment = null;
        }
        return new T_Url($scheme,$host,$path,$parameters,$fragment);
    }

}