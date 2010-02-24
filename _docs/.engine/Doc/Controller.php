<?php
/**
 * Contains the doc controller base.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Default base controller.
 *
 * @package docs
 */
class Doc_Controller extends T_Controller
{

    protected function HEAD($response)
    {
        $response->setContent(null);
    }

    protected function respondWithStatus($status,T_Response $response)
    {
        if ($status==404) {
            $response->abort();
            $new = new T_Response(404);
            $new->setContent(new T_Template_File($this->find('404','tpl')));
            throw $new;
        }
        parent::respondWithStatus($status,$response);
    }


}
