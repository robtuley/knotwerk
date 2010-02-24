<?php
class Test_Navigation extends T_Unit_Case
{

    protected function getSampleNav()
    {
        return new Navigation(new T_Url('http','example.com'),
                              new Package_Gateway());
    }

    function testCanGetNavigation()
    {
        $nav = $this->getSampleNav();
        $this->assertTrue($nav->get() instanceof T_Url_Collection);
    }

    function testHowToAreaIsSetupWithSubNavAreas()
    {
        $nav = $this->getSampleNav()->get()->howto;
        $this->assertTrue($nav->isChildren());
    }

    function testQAAreaIsSetupWithSubNavAreas()
    {
        $nav = $this->getSampleNav()->get()->qa;
        $this->assertTrue($nav->isChildren());
    }

    function testRefAreaIsSetupWithSubNavAreasForEachPackage()
    {
        $nav = $this->getSampleNav()->get()->ref;
        $this->assertTrue($nav->isChildren());
        $gw = new Package_Gateway();
        foreach ($gw->getAll() as $p) {
            $this->assertTrue($nav->{$p->getAlias()} instanceof T_Url_Collection);
        }
    }

    function testUrlGetsXhtmlUrl()
    {
        $nav = $this->getSampleNav();
        $url = $nav->get();
        $f = new T_Filter_Xhtml();
        // root
        $this->assertSame($nav->url(),$url->getUrl($f));
        // one level down
        $this->assertSame($nav->url('howto'),$url->howto->getUrl($f));
    }

}
