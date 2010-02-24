<?php
/**
 * Contains the T_Xhtml_UrlSitemap class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An XML sitemap visitor.
 *
 * This class generates an XML sitemap based on a composite XHTML URL tree.
 *
 * @see http://www.sitemaps.org/protocol.php
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_UrlSitemap extends T_Xml implements T_Visitor,T_View
{

    /**
     * A filter to escape URL strings.
     *
     * @var T_Filter_Skeleton
     */
    protected $url_filter;

    /**
     * Create XML file header.
     */
    function __construct()
    {
        $this->url_filter = new T_Filter_Xhtml();
        $xml = "<?xml version='1.0' encoding='".T_CHARSET."'?>".
          '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
          'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
          ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
          'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        parent::__construct($xml);
    }

    /**
     * Outputs the view to the output buffer.
     *
     * @return T_Xhtml_UrlSitemap  fluent interface
     */
    function toBuffer()
    {
        echo $this->__toString();
        return $this;
    }

    /**
     * Default rendering of a node.
     *
     * This renders a URL into a XML sitemap node.
     * <code>
     * <url>
     *   <loc>http://www.example.com/</loc>
     *   <changefreq>monthly</changefreq>
     *   <priority>0.8</priority>
     * </url>
     * </code>
     *
     * @param T_CompositeLeaf $node  node to visit
     */
    function renderDefault(T_CompositeLeaf $node)
    {
        $url = $this->addChild('url');
        $loc = $node->getUrl($this->url_filter);
        $url->addChild('loc',$loc);
        $freq = $node->getChangeFreq();
        if (!is_null($freq)) {
            $url->addChild('changefreq',$freq);
        }
        $url->addChild('priority',$node->getPriority());
    }

    /**
     * Pre-Child visitor event.
     */
    function preChildEvent()
    {
        // nothing to do.
    }

    /**
     * Post-Child visitor event.
     */
    function postChildEvent()
    {
        // nothing to do.
    }

    /**
     * Always traverse children.
     *
     * @return bool  whether to traverse composite children.
     */
    function isTraverseChildren()
    {
        return true;
    }

    /**
     * Renders a node through default renderer.
     *
     * This function passes any "visit" prefixed function through to the default
     * render, and any other method call is passed to the parent
     * SimpleXmlElement function call handler.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        if (strcmp(substr($method,0,5),'visit')===0) {
            return $this->renderDefault($arg[0]);
        } else {
            return parent::__call($method,$arg);
        }
    }

}