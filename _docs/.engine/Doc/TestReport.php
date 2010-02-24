<?php
/**
 * Contains the test report controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */
class Doc_TestReport extends Doc_Controller
{

    function getLog()
    {
        return $this->context->getLog();
    }

    function isGreenLight()
    {
        return $this->context->isGreenLight();
    }

    function GET($response)
    {
        if (!($log=$this->getLog())) {
            $this->respondWithStatus(404,$response);
        }
        $view = $response->getContent();
        $view->title = 'Unit Test History';
        $report = new T_Template_File($this->find('test_report','tpl'));
        $report->log = $this->getLog();
        $report->green = $this->isGreenLight();
        $view->primary->content = $report;
    }

}
