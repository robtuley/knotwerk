<?php
/**
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */
class Doc_MapTarget extends Doc_Controller
{

    protected $prev = false;
    protected $item = false;
    protected $next = false;

    function handleRequest($response)
    {
        $section = $this->context->getSection();
        $map = $this->context->getMap();
        $alias = _end($this->getUrl()->getPath());

        // populate item, next foreach
        foreach ($map as $item) {
            if (strcmp($item->alias,$alias)===0) {
                $this->item = $item;
            } elseif ($this->item===false) {
                $this->prev = $item;
            } elseif ($this->next===false) {
                $this->next = $item;
            } else {
                break; // out of foreach
            }
        }

        // check is found
        if ($this->item===false) {
            $this->respondWithStatus(404,$response);
        }

        // activate navigation
        $nav = $this->like('Navigation');
        $section = $nav->get()->$section->$alias->setActive();
        $nav->appendCrumb($section->getTitle(),$this->url->getUrl());

        parent::handleRequest($response);
    }

    function GET($response)
    {
        $view = $response->getContent();
        $view->title = (string) $this->item->name;

        // parse out filename
        list($filename,$ext) = explode('.',$this->item->file);
        $dir = DOC_DIR.$this->context->getSection();
        $file = new T_File_Path($dir,$filename,$ext);

        // setup template
        $render = new T_Template_File($this->find('map_target','tpl'));
        $render->text = $file->getContent(new Filter_AsFormattedText());
        $render->item = $this->item;
        $render->next = $this->next;
        $render->prev = $this->prev;
        $render->section = $this->context->getSection();

        $view->primary->content = $render;
    }

}
