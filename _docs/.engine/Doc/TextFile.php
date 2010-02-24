<?php
/**
 * Contains the Text controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

class Doc_TextFile extends Doc_Controller
{

    function parseFilePath(T_Response $response)
    {
        $section = _end($this->getUrl()->getPath());
        $file = new T_File_Path(DOC_DIR,$section,'txt');
        if (!$file->exists()) {
            $this->respondWithStatus(404,$response);
        }

        // register nav section
        $section = str_replace('-','',$section);
        $nav = $this->like('Navigation')->get();
        if (isset($nav->$section)) {
            $nav->$section->setActive();
        }

        return $file;
    }

    function GET($response)
    {
        $file = $this->parseFilePath($response);
        $view = $response->getContent();
        $content = $file->getContent(new Filter_AsFormattedText());
        $title = new Text_Title();
        $content->accept($title);
        $view->title = $title->__toString();
        $view->primary = new T_Template_File($this->find('textfile','tpl'));
        $view->primary->text = $content;
    }

}
