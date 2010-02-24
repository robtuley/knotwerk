<?php
/**
 * Unit test cases for the T_Session_Handler class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Session_Handler test cases.
 *
 * @package coreTests
 */
class T_Test_Session_Handler extends T_Unit_Case
{

    /**
     * Gets a default session object.
     *
     * @param array $data
     * @return T_Session_Handler
     */
    function getDefaultSession($data)
    {
        $session = new T_Session_Handler();
        $session->addDriver(new T_Test_Session_DriverStub($data));
        return $session;
    }

    function testExistsFalseWhenNotSet()
    {
        $session = $this->getDefaultSession(array());
        $this->assertFalse($session->exists('key'));
        $this->assertFalse($session->exists('ns|key'));
        $this->assertFalse($session->exists('altNs|key'));
    }

    function testExistsTrueWhenIsSet()
    {
        $data = array('ns|int'=>1,'str'=>'hello');
        $session = $this->getDefaultSession($data);
        $this->assertTrue($session->exists('str'));
        $this->assertFalse($session->exists('notset'));
        $this->assertTrue($session->exists('ns|int'));
        $this->assertFalse($session->exists('ns|notset'));
    }

    function testExistsTrueWhenSetAsNull()
    {
        $data = array('ns|int'=>null,'str'=>null);
        $session = $this->getDefaultSession($data);
        $this->assertTrue($session->exists('str'));
        $this->assertTrue($session->exists('ns|int'));
    }

    function testCanSetAndGetNormalKey()
    {
        $session = $this->getDefaultSession(array());
        $session->set('str','hello');
        $session->set('int',1);
        $this->assertSame('hello',$session->get('str'));
        $this->assertSame(1,$session->get('int'));
    }

    function testCanOverwriteNormalKey()
    {
        $session = $this->getDefaultSession(array());
        $session->set('str','hello');
        $session->set('str','diff');
        $this->assertSame('diff',$session->get('str'));
    }

    function testCanFilterDataOnRetrievalWithNormalKey()
    {
        $session = $this->getDefaultSession(array('str'=>'hello'));
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform('hello'),$session->get('str',$f));
    }

    function testCanSetAndGetNamespacedKey()
    {
        $session = $this->getDefaultSession(array());
        $session->set('ns|str','hello');
        $session->set('ns|int',1);
        $this->assertSame('hello',$session->get('ns|str'));
        $this->assertSame(1,$session->get('ns|int'));
    }

    function testCanOverwriteNamespacedKey()
    {
        $session = $this->getDefaultSession(array());
        $session->set('ns|str','hello');
        $session->set('ns|str','diff');
        $this->assertSame('diff',$session->get('ns|str'));
    }

    function testCanFilterDataOnRetrievalWithNamespacedKey()
    {
        $session = $this->getDefaultSession(array('ns|str'=>'hello'));
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform('hello'),$session->get('ns|str',$f));
    }

    function testDoubleNamespaceDoesNotCauseError()
    {
        $session = $this->getDefaultSession(array());
        $session->set('ns|str|value','hello');
        $this->assertSame('hello',$session->get('ns|str|value'));
    }

    function testSetMethodHasAFluentInterface()
    {
        $session = $this->getDefaultSession(array());
        $test = $session->set('name','value');
        $this->assertSame($test,$session);
        $test = $session->set('ns|name','value');
        $this->assertSame($test,$session);
    }

    function testCanDeleteNormalData()
    {
        $session = $this->getDefaultSession(array());
        $session->set('str','hello');
        $this->assertTrue($session->exists('str'));
        $session->delete('str');
        $this->assertFalse($session->exists('str'));
    }

    function testCanDeleteNamespacedData()
    {
        $session = $this->getDefaultSession(array());
        $session->set('ns|str','hello');
        $this->assertTrue($session->exists('ns|str'));
        $session->delete('ns|str');
        $this->assertFalse($session->exists('ns|str'));
    }

    function testNoErrorWhenDeleteNotSetValue()
    {
        $session = $this->getDefaultSession(array());
        $session->delete('str');
        $session->delete('ns|str');
    }

    function testDeleteMethodHasAFluentInterface()
    {
        $session = $this->getDefaultSession(array());
        $test = $session->delete('str');
        $this->assertSame($session,$test);
    }

    function testRegenerateCascadesToSingleSessionDriver()
    {
        $driver = new T_Test_Session_DriverStub(array());
        $session = new T_Session_Handler();
        $session->addDriver($driver);
        $this->assertFalse($driver->isRegenerated());
        $session->regenerate();
        $this->assertTrue($driver->isRegenerated());
    }

    function testRegenerateCascadesToMultiSessionDriver()
    {
        $driver1 = new T_Test_Session_DriverStub(array());
        $driver2 = new T_Test_Session_DriverStub(array());
        $session = new T_Session_Handler();
        $session->addDriver($driver1);
        $session->addDriver($driver2,'ns');
        $this->assertFalse($driver1->isRegenerated());
        $this->assertFalse($driver2->isRegenerated());
        $session->regenerate();
        $this->assertTrue($driver1->isRegenerated());
        $this->assertTrue($driver2->isRegenerated());
    }

    function testRegenerateMethodHasAFluentInterface()
    {
        $session = new T_Session_Handler();
        $session->addDriver(new T_Test_Session_DriverStub(array()));
        $test = $session->regenerate();
        $this->assertSame($test,$session);
    }

    function testDestroyCascadesToSingleSessionDriver()
    {
        $driver = new T_Test_Session_DriverStub(array());
        $session = new T_Session_Handler();
        $session->addDriver($driver);
        $this->assertFalse($driver->isDestroyed());
        $session->destroy();
        $this->assertTrue($driver->isDestroyed());
    }

    function testDestroyCascadesToMultiSessionDriver()
    {
        $driver1 = new T_Test_Session_DriverStub(array());
        $driver2 = new T_Test_Session_DriverStub(array());
        $session = new T_Session_Handler();
        $session->addDriver($driver1);
        $session->addDriver($driver2,'ns');
        $this->assertFalse($driver1->isDestroyed());
        $this->assertFalse($driver2->isDestroyed());
        $session->destroy();
        $this->assertTrue($driver1->isDestroyed());
        $this->assertTrue($driver2->isDestroyed());
    }

    function testDestroyMethodHasAFluentInterface()
    {
        $session = new T_Session_Handler();
        $session->addDriver(new T_Test_Session_DriverStub(array()));
        $test = $session->destroy();
        $this->assertSame($test,$session);
    }

    // adding drivers and getting/setting data:

    function testAddDriverMethodHasAFluentInterface()
    {
        $driver = new T_Test_Session_DriverStub(array());
        $session = new T_Session_Handler();
        $test = $session->addDriver($driver);
        $this->assertSame($session,$test);
    }

    function testAllDataSavedToDefaultDriver()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $driver = new T_Test_Session_DriverStub($original);
        $session = new T_Session_Handler();
        $session->addDriver($driver);
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2',
                       'diff|name'=>'diff ns value');
        foreach ($added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($driver->getData(),$original+$added);
    }

    function testDefaultDriverCanBeReplaced()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $driver1 = new T_Test_Session_DriverStub(array());
        $driver2 = new T_Test_Session_DriverStub($original);
        $session = new T_Session_Handler();
        $session->addDriver($driver1)
                ->addDriver($driver2);
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2',
                       'diff|name'=>'diff ns value');
        foreach ($added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($driver1->getData(),array());
        $this->assertEquals($driver2->getData(),$original+$added);
    }

    function testEmptyArraySavedToDefaultDriverIfNoData()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $driver = new T_Test_Session_DriverStub($original);
        $session = new T_Session_Handler();
        $session->addDriver($driver)
                ->delete('name')
                ->delete('ns|name');
        unset($session); // destruct
        $this->assertSame($driver->getData(),array());
    }

    function testSingleExtraNamespaceDriverAddedToGetAndRetrieveData()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $alt_original = array('diff|name'=>'this is different value',
                              'diff|maintain'=>'this stays');
        $default = new T_Test_Session_DriverStub($original);
        $alt = new T_Test_Session_DriverStub($alt_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt,'diff');
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2');
        $alt_added = array('diff|name'=>'diff value 1',
                           'diff|name2'=>'diff value 2');
        foreach ($added as $key=>$value) $session->set($key,$value);
        foreach ($alt_added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt->getData(),$alt_added+$alt_original);
    }

    function testSingleExtraNamespaceDriverCanBeReplaced()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $alt_original = array('diff|name'=>'this is different value',
                              'diff|maintain'=>'this stays');
        $default = new T_Test_Session_DriverStub($original);
        $alt1 = new T_Test_Session_DriverStub(array());
        $alt2 = new T_Test_Session_DriverStub($alt_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt1,'diff')
                ->addDriver($alt2,array('diff','other'));
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2');
        $alt_added = array('diff|name'=>'diff value 1',
                           'diff|name2'=>'diff value 2');
        foreach ($added as $key=>$value) $session->set($key,$value);
        foreach ($alt_added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt2->getData(),$alt_added+$alt_original);
        $this->assertEquals($alt1->getData(),array());
    }

    function testSingleExtraNamespaceDriverSavesEmptyArrayWhenNotData()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $alt_original = array('diff|name'=>'value',
                              'diff|name2'=>'value');
        $default = new T_Test_Session_DriverStub($original);
        $alt = new T_Test_Session_DriverStub($alt_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt,'diff');
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2');
        foreach ($added as $key=>$value) $session->set($key,$value);
        $session->delete('diff|name')->delete('diff|name2');
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt->getData(),array());
    }

    function testSingleDriverCanBeRegisteredForMultiNamespaces()
    {
        $original = array('name'=>'value');
        $alt_original = array('ns|name'=>'ns name',
                              'alt|name'=>'alt name');
        $default = new T_Test_Session_DriverStub($original);
        $alt = new T_Test_Session_DriverStub($alt_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt,array('ns','alt'));
        $added = array('name2'=>'value 2',
                       'other_name2'=>'value 3');
        $alt_added = array('ns|other'=>'diff value 1',
                           'alt|other'=>'diff value 2');
        foreach ($added as $key=>$value) $session->set($key,$value);
        foreach ($alt_added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt->getData(),$alt_added+$alt_original);
    }

    function testAddDriverTwiceToAddNamespaces()
    {
        $original = array('name'=>'value');
        $alt_original = array('ns|name'=>'ns name',
                              'alt|name'=>'alt name');
        $default = new T_Test_Session_DriverStub($original);
        $alt = new T_Test_Session_DriverStub($alt_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt,'ns')
                ->AddDriver($alt,'alt');
        $added = array('name2'=>'value 2',
                       'other_name2'=>'value 3');
        $alt_added = array('ns|other'=>'diff value 1',
                           'alt|other'=>'diff value 2');
        foreach ($added as $key=>$value) $session->set($key,$value);
        foreach ($alt_added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt->getData(),$alt_added+$alt_original);
    }

    function testMultiDriversCanBeUsedForDifferentNamespaces()
    {
        $original = array('name'=>'value');
        $alt_original = array('ns|name'=>'ns name',
                              'alt|name'=>'alt name');
        $alt2_original = array('ns2|name'=>'ns2 name',
                               'alt2|name'=>'alt2 name');
        $default = new T_Test_Session_DriverStub($original);
        $alt = new T_Test_Session_DriverStub($alt_original);
        $alt2 = new T_Test_Session_DriverStub($alt2_original);
        $session = new T_Session_Handler();
        $session->addDriver($default)
                ->addDriver($alt,'ns')
                ->addDriver($alt,'alt')
                ->addDriver($alt2,array('ns2','alt2'));
        $added = array('name2'=>'value 2',
                       'other_name2'=>'value 3');
        $alt_added = array('ns|other'=>'diff value 1',
                           'alt|other'=>'diff value 2');
        $alt2_added = array('ns2|other'=>'diff value 4',
                            'alt2|other'=>'diff value 5');
        foreach ($added+$alt_added+$alt2_added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($default->getData(),$original+$added);
        $this->assertEquals($alt->getData(),$alt_added+$alt_original);
        $this->assertEquals($alt2->getData(),$alt2_added+$alt2_original);
    }


    function testCannotAddDriverAfterSessionHasStarted()
    {
        $session = $this->getDefaultSession(array());
        $session->set('name','value');
        try {
            $session->addDriver(new T_Test_Session_DriverStub(array()));
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

    function testIfNoDefaultDriverLastDriverInStackIsUsed()
    {
        $original = array('name'=>'value','ns|name'=>'ns value');
        $driver = new T_Test_Session_DriverStub($original);
        $session = new T_Session_Handler();
        $session->addDriver(new T_Test_Session_DriverStub(array()),'other')
                ->addDriver($driver,'ns');
        $added = array('name2'=>'value2',
                       'ns|name2'=>'ns value 2',
                       'diff|name'=>'diff ns value');
        foreach ($added as $key=>$value) $session->set($key,$value);
        unset($session); // destruct
        $this->assertEquals($driver->getData(),$original+$added);
    }

}