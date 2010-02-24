<?php
/**
 * Contains the T_Response_Redirect class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * HTTP Redirect.
 *
 * This class encapsulates a redirect response.
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Response_Redirect extends T_Response
{

    /**
     * Initialise redirect request.
     *
     * Note that the redirect URL must be an ABSOLUTE rather than relative URL.
     * IIS under CGI mode has a bug which will crash when redirecting to
     * relative URLs.
     *
     * @param string $url  URL to redirect to
     */
    function __construct($url)
    {
        $f = new T_Filter_Xhtml();
        $content = '<html><head>'
                  .'<meta http-equiv="Refresh" content="1;url='._transform($url,$f).'">'
                  .'</head><body>'
                  .'<a href="'._transform($url,$f).'">Continue &rsaquo;</a>'
                  .'</body></html>';
        parent::__construct(303);
        $this->setHeader("Location", $url);
        $this->setContent($content);
    }

}