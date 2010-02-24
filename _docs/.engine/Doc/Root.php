<?php
/**
 * Contains the Root controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * HTTP Root Dispatcher.
 *
 * @package docs
 */
class Doc_Root extends Doc_Controller
{

    /**
     * Handles the request.
     *
     * @param T_Response $response
     * @throws T_Response
     */
    function handleRequest($response)
    {
        $nav = $this->like('Navigation',array('root'=>$this->getAppRoot()));
        $this->willUse($nav);

        // create base template
        $view = new View_Master($this);
        $response->setContent($view);

        parent::handleRequest($response);
    }

    /**
     * Executes a GET request.
     *
     * @param T_Response $response  response to build.
     */
    function GET($response)
    {
        $this->like('Navigation')->get()->feature->setActive();
        $view = $response->getContent();
        $view->title = 'Knotwerk PHP5 Library';
        $view->primary = new T_Template_File($this->find('home','tpl'));
    }

    /**
     * Map next segment to classname.
     *
     * @param string $name  URL segment to map to a classname
     * @return string   controller classname
     */
    function mapToClassname($name)
    {
        if (strcmp($name,'sitemap.xml')==0) return 'Doc_SiteMap';
        if (strcmp($name,'ref')==0) return 'Doc_Source';
        if (strcmp($name,'download')==0) return 'Doc_Download';
        if (strcmp($name,'how-to')==0) return 'Doc_HowTo';
        if (strcmp($name,'qa')==0) return 'Doc_Qa';
        return 'Doc_TextFile';
    }

}
