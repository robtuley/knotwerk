<?php
/**
 * Unit test cases for T_Form_Mime class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Mime unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Mime extends T_Unit_Case
{

    function testDefaultMimeTypeIsUrlEncoded()
    {
        $mime = new T_Form_Mime();
        $this->assertSame('application/x-www-form-urlencoded',$mime->__toString());
    }

    function testMimeIsUrlEncodedWhenVisitsNormalForm()
    {
        $mime = new T_Form_Mime();
        $form = new T_Form_Post('alias','label');
        $form->addChild(new T_Form_Text('child','label'));
        $form->accept($mime);
        $this->assertSame('application/x-www-form-urlencoded',$mime->__toString());
    }

    function testMimeIsMultipartWhenVisitsFormWithFileUpload()
    {
        $mime = new T_Form_Mime();
        $form = new T_Form_Post('alias','label');
        $form->addChild(new T_Form_Text('child','label'));
        $form->addChild(new T_Form_Upload('file','label'));
        $form->accept($mime);
        $this->assertSame('multipart/form-data',$mime->__toString());
    }

}