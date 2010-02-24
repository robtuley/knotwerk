<?php
/**
 * Contains the Sitemap controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * HTTP Sitemap.
 *
 * @package textcaptcha
 */
class Doc_Sitemap extends T_Controller
{

    /**
     * Executes a GET request.
     *
     * @param T_Response $response  response to build.
     */
    protected function GET($response)
    {
        $response->setHeader('Content-Type','text/xml');
        $xml = new T_Xhtml_UrlSitemap();
        $this->like('Navigation')->get()->accept($xml);
        $response->setContent($xml);
    }

    /**
     * Executes a HEAD request.
     *
     * @param OKT_Response $response  response to build.
     */
    protected function HEAD($response)
    {
        $response->setHeader('Content-Type','text/xml');
        $response->setContent(null);
    }

    /**
     * No sub-nav for this section.
     *
     * @param string $name  URL segment to map to a classname
     * @return string   controller classname
     */
    protected function mapToClassname($name)
    {
        false;
    }

}
