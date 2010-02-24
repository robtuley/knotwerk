<?php
/**
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

abstract class Doc_Map extends Doc_Controller
{

    protected $map = false;

    function handleRequest($response)
    {
        $nav = $this->like('Navigation');
        $section = $nav->get()->{$this->getSection()}->setActive();
        $nav->appendCrumb($section->getTitle(),$this->url->getUrl());
        parent::handleRequest($response);
    }

    function getMap()
    {
        if ($this->map===false) {
            $path = DOC_DIR.$this->getSection().'/.map.xml';
            $this->map = simplexml_load_file($path);
        }
        return $this->map;
    }

    abstract function getSection();

    function mapToClassname($name)
    {
        return 'Doc_MapTarget';
    }

}
