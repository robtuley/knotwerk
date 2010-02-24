<?php
class T_Test_Cage_PostStub extends T_Cage_Post implements T_Test_Stub
{

    /**
     * Encapsulate post and files superglobal.
     *
     * @param array $files
     */
    function __construct(array $post,array $files)
    {
        $this->data = $post;
        $this->files = $files;
    }

}
