<?php
/**
 * Defines the T_Test_User class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Unit test cases for the T_User class.
 *
 * @package ACL
 */
class T_Test_User extends T_Unit_Case
{

    function testEmailAndIdSetInConstructor()
    {
        $user = new T_User(12,'rob@example.com');
        $this->assertSame(12,$user->getId());
        $this->assertSame('rob@example.com',$user->getEmail());
    }

    function testIdCanBeChanged()
    {
        $user = new T_User(12,'rob@example.com');
        $this->assertSame($user,$user->setId(34),'fluent');
        $this->assertSame(34,$user->getId());
    }

    function testEmailCanBeChanged()
    {
        $user = new T_User(12,'rob@example.com');
        $this->assertSame($user,$user->setEmail('alt@ex.com'),'fluent');
        $this->assertSame('alt@ex.com',$user->getEmail());
    }

    function testEmailCanBeFilteredOnRetrieval()
    {
        $user = new T_User(12,'rob@example.com');
        $f = new T_Test_Filter_Suffix('suffix');
        $this->assertSame($f->transform($user->getEmail()),
                          $user->getEmail($f)              );
    }

}
