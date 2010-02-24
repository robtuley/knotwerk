<?php
/**
 * Contains the Download controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Download controller.
 *
 * @package docs
 */
class Doc_Download extends Doc_Controller
{

    function handleRequest($response)
    {
        $nav = $this->like('Navigation');
        $section = $nav->get()->download->setActive();
        $nav->appendCrumb($section->getTitle(),$this->url->getUrl());
        parent::handleRequest($response);
    }

    function GET($response)
    {
        $view = $response->getContent();
        $view->title = 'Download Knotwerk Codebase';
        $view->primary = new T_Template_File($this->find('download','tpl'));
    }

}
