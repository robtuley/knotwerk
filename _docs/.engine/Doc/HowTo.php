<?php
/**
 * Contains the HowTo controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

class Doc_HowTo extends Doc_Map
{

    function getSection()
    {
        return 'howto';
    }

    function handleRequest($response)
    {
        $view = $response->getContent();
        $view->primary = new T_Template_File($this->find('howto','tpl'));
        $view->primary->map = $this->getMap();
        parent::handleRequest($response);
    }

    function GET($response)
    {
        $view = $response->getContent();
        $view->title = 'How To';
        $index = new T_Template_File($this->find('map_index','tpl'));
        $index->title = 'How To.. Do Just About Anything';
        $index->section = $this->getSection();
        $index->map = $this->getMap();
        $view->primary->content = $index;
    }

}
