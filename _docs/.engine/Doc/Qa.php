<?php
/**
 * Contains the QA controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */
class Doc_Qa extends Doc_Map
{

    protected $log = null;
    protected $map_init = false;

    function getLog()
    {
        if (is_null($this->log)) {
            $path = T_ROOT_DIR.'.log.xml';
            if ($this->log = file_exists($path)) {
                $this->log = simplexml_load_file($path);
            }
        }
        return $this->log;
    }

    function isGreenLight()
    {
        $sxe = $this->getLog();
        if (false===$sxe) return true; // no tests, assume OK
        // get last entry
        $entry = $sxe->test[(count($sxe->test)-1)];
        return ($entry->failed==0 && $entry->errored==0);
    }

    function getMap()
    {
        $map = parent::getMap();
        if (false===$this->map_init) {
            if ($this->getLog()) {
                $link = $map->addChild('link');
                $link->addChild('alias','report');
                $link->addChild('name','Unit Test Report');
                $link->addChild('desc','Provides details of the unit test history of this deployment');
            }
            $this->map_init = true;
        }
        return $map;
    }

    function getSection()
    {
        return 'qa';
    }

    function handleRequest($response)
    {
        $view = $response->getContent();
        $view->primary = new T_Template_File($this->find('qa','tpl'));
        $view->primary->map = $this->getMap();
        $qa = $this->like('Navigation')->get()->qa;
        $view->primary->report = isset($qa->report) ? $qa->report : false;
        $view->primary->green = $this->isGreenLight();
        parent::handleRequest($response);
    }

    function GET($response)
    {
        $view = $response->getContent();
        $view->title = 'Quality Assurance';
        $index = new T_Template_File($this->find('map_index','tpl'));
        $index->title = 'Quality Assurance';
        $index->section = $this->getSection();
        $index->map = $this->getMap();
        $view->primary->content = $index;
    }

    function mapToClassname($name)
    {
        if (strcmp($name,'report')===0 && $this->getLog()) {
            return 'Doc_TestReport';
        }
        return parent::mapToClassname($name);
    }

}
