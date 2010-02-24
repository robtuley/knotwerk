<?php
/**
 * This tests an example extended pwd user gateway with extra fields
 * (including sub-objects ID).
 *
 * @version SVN: $Id$
 */

/**
 * Stub classes
 */
class T_Test_User_Phone_Stub implements T_Test_Stub
{
    protected $num;
    protected $ext;
    function __construct($num,$ext)
    {
        $this->num = $num;
        $this->ext = $ext;
    }
    function getNum()
    {
        return $this->num;
    }
    function getExt()
    {
        return $this->ext;
    }
}
class T_Test_User_Ctry_Stub implements T_Test_Stub
{
    protected $id;
    function __construct($id)
    {
        $this->id = $id;
    }
    function getId()
    {
        return $this->id;
    }
}
class T_Test_User_Stub extends T_User implements T_Test_Stub
{
    protected $salt;
    protected $name;
    protected $ctry;
    protected $phone;
    function __construct($id,$email,$salt,$name,$country,$phone)
    {
        parent::__construct($id,$email);
        $this->salt = $salt;
        $this->name = $name;
        $this->ctry = $country;
        $this->phone = $phone;
    }
    function getSalt()
    {
        return $this->salt;
    }
    function getName()
    {
        return $this->name;
    }
    function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    function getCountry()
    {
        return $this->ctry;
    }
    function setCountry($ctry)
    {
        $this->ctry = $ctry;
        return $this;
    }
    function getPhone()
    {
        return $this->phone;
    }
    function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
}
class T_Test_User_Gateway_Stub extends T_User_PwdGateway implements T_Test_Stub
{
    protected function getFieldsFor($type)
    {
        $fields = parent::getFieldsFor($type);
        $fields[] = 'name';
        $fields[] = 'country';
        $fields['phone'] = array('num','ext');
        return $fields;
    }
    protected function toUser($row)
    {
        if ($row['country']) {
            $row['country'] = new T_Test_User_Ctry_Stub($row['country']);
        }
        $row['phone'] = null;
        if ($row['phone_num'] || $row['phone_ext']) {
            $row['phone'] = new T_Test_User_Phone_Stub($row['phone_num'],
                                                         $row['phone_ext']);
        }
        return parent::toUser($row);
    }
}

/**
 * Tests.
 */
class T_Test_User_PwdGateway extends T_Test_User_Gateway
{

    function setUpSuite()
    {
        // setup DBs with necessary SQL
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->setupSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);

        // create gatways to cycle over
        $gateways = array();
        $di = new T_Factory_Di();
        $di->willUse('T_Test_User_Stub');
        foreach ($this->dbs as $db) {
            $m = $db->master();
            $m->query('ALTER TABLE person ADD pwd VARCHAR(40)');
            $m->query('ALTER TABLE person ADD salt TEXT');
            $m->query('ALTER TABLE person ADD name TEXT');
            $m->query('ALTER TABLE person ADD country INT');
            $m->query('ALTER TABLE person ADD phone_num TEXT');
            $m->query('ALTER TABLE person ADD phone_ext TEXT');
            $gateways[] = new T_Test_User_Gateway_Stub($db,$di);
        }
        $this->cycleOn('gw',$gateways);
    }

    function createUser()
    {
        return new T_Test_User_Stub(null,
                          uniqid('us\'er',true).'@example.com',
                          uniqid('sa\'lt',true),
                          'Some \'name',
                          new T_Test_User_Ctry_Stub(3),
                          new T_Test_User_Phone_Stub('12\'345','123'));
    }

    function testIfObjectIsNullAllSubFieldsSetToNullOnInsert($gw)
    {
        $user = $this->createUser();
        $user->setPhone(null)->setCountry(null);
        $gw->save($user);
        $this->assertEquals($user,$gw->getById($user->getId()));
    }

    function testIfObjectIsNullAllSubFieldsSetToNullOnUpdate($gw)
    {
        $gw->save($user=$this->createUser());
        $user->setPhone(null)->setCountry(null);
        $gw->save($user);
        $this->assertEquals($user,$gw->getById($user->getId()));
    }

    function testSubObjectCanBePartiallyNullOnInsertUpdate($gw)
    {
        $user = $this->createUser();
        $user->setPhone(new T_Test_User_Phone_Stub('12345',null));
        $gw->save($user);
        $this->assertEquals($user,$gw->getById($user->getId()));
        $user->setPhone(new T_Test_User_Phone_Stub(null,'123'));
        $gw->save($user);
        $this->assertEquals($user,$gw->getById($user->getId()));
    }

    function testIsPwdReturnsFalseAlwaysWhenPwdNotSet($gw)
    {
        $gw->save($user=$this->createUser());
        $this->assertFalse($gw->isPwd('value',$user));
        $this->assertFalse($gw->isPwd('other',$user));
    }

    function testIsPwdReturnsTrueWhenGivenCorrectPwd($gw)
    {
        $gw->save($user=$this->createUser());
        $this->assertSame($gw,$gw->setPwd(' pwd ',$user));
        $this->assertFalse($gw->isPwd('value',$user));
        $this->assertFalse($gw->isPwd('p\'wd',$user));
        $this->assertTrue($gw->isPwd(' pwd ',$user));
    }

    function testGetByAuthWhenUserDoesNotExist($gw)
    {
        try {
            $gw->authenticate('notanemail','pwd');
            $this->fail();
        } catch (T_Exception_Auth $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testGetByAuthWhenPwdIsNotCorrect($gw)
    {
        $gw->save($user=$this->createUser());
        try {
            $gw->authenticate($user->getEmail(),'pwd');
            $this->fail();
        } catch (T_Exception_Auth $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testGetByAuthWhenPwdIsCorrect($gw)
    {
        $gw->save($user=$this->createUser());
        $gw->setPwd('pwd',$user);
        $auth = $gw->authenticate($user->getEmail(),'pwd');
        $expect = new T_Auth(T_Auth::CHALLENGED,$user);
        $this->assertEquals($expect,$auth);
    }

    function testAuthObservedWhenUserDoesNotExist($gw)
    {
        $obs = array(new T_Test_Auth_Observer(),
                     new T_Test_Auth_Observer() );
        foreach ($obs as $o) {
            $this->assertSame($gw,$gw->attach($o));
        }
        try {
            $gw->authenticate('notanemail','pwd');
        } catch (T_Exception_Auth $e) {
            $expect = new T_Auth(T_Auth::CHALLENGED);
            foreach ($obs as $o) {
                $this->assertEquals($expect,$o->getFail());
                $this->assertFalse($o->getPass());
            }
        }
    }

    function testPwdIsNotCorrectIsObserved($gw)
    {
        $obs = array(new T_Test_Auth_Observer(),
                     new T_Test_Auth_Observer() );
        foreach ($obs as $o) {
            $this->assertSame($gw,$gw->attach($o));
        }
        $gw->save($user=$this->createUser());
        try {
            $gw->authenticate($user->getEmail(),'pwd');
        } catch (T_Exception_Auth $e) {
            $expect = new T_Auth(T_Auth::CHALLENGED,$user);
            foreach ($obs as $o) {
                $this->assertEquals($expect,$o->getFail());
                $this->assertFalse($o->getPass());
            }
        }
    }

    function testPwdIsCorrectIsObserved($gw)
    {
        $obs = array(new T_Test_Auth_Observer(),
                     new T_Test_Auth_Observer() );
        foreach ($obs as $o) {
            $this->assertSame($gw,$gw->attach($o));
        }
        $gw->save($user=$this->createUser());
        $gw->setPwd('pwd',$user);
        $auth = $gw->authenticate($user->getEmail(),'pwd');
        $expect = new T_Auth(T_Auth::CHALLENGED,$user);
        foreach ($obs as $o) {
            $this->assertEquals($expect,$o->getPass());
            $this->assertFalse($o->getFail());
        }
    }











}
