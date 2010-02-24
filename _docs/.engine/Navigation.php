<?php
/**
 * Defines the Navigation class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Registry.
 *
 * @package docs
 */
class Navigation
{

    protected $nav;
    protected $crumbs = array();

    /**
     * Create navigation.
     *
     * @param T_Url $root  root URL
     * @param Package_Gateway  $gw  package gateway
     */
    function __construct(T_Url $root,Package_Gateway $gw)
    {
        $nav = new T_Url_Collection('knotwerk.com',
                                    $root->getScheme(),
                                    $root->getHost(),
                                    $root->getPath());

        $features = new T_Url_Collection('Features',
                                    $root->getScheme(),
                                    $root->getHost(),
                                    $root->getPath());
        $nav->addChild($features,'feature');

        $ref = $nav->createByAppendPath('ref','Reference');
        foreach ($gw->getAll() as $p) {
            $sub = $ref->createByAppendPath($p->getAlias(),$p->getName());
            $ref->addChild($sub,$p->getAlias());
        }
        $nav->addChild($ref,'ref');

        $howto = $nav->createByAppendPath('how-to','How To...');
        $this->populateFromDir(DOC_DIR.'howto/',$howto);
        $nav->addChild($howto,'howto');

        $download = $nav->createByAppendPath('download','Download');
        $nav->addChild($download,'download');

        $qa = $nav->createByAppendPath('qa','QA');
        $this->populateFromDir(DOC_DIR.'qa/',$qa);
        // test report
        if (file_exists(T_ROOT_DIR.'.log.xml')) {
            $report = $qa->createByAppendPath('report','Unit Test Report');
            $qa->addChild($report,'report');
        }
        $nav->addChild($qa,'qa');

        $licence = $nav->createByAppendPath('licence','Licence');
        $nav->addChild($licence,'licence');

        $this->nav = $nav;
    }

    /**
     * Gets navigation.
     *
     * @return T_Url_Collection
     */
    function get()
    {
        return $this->nav;
    }

    /**
     * Adds navigation items from the map XML items.
     *
     * @return Navigation
     */
    protected function populateFromDir($dir,$url)
    {
        $map = $dir.'.map.xml';
        if (is_file($map)) {
            $map = new SimpleXMLElement(file_get_contents($map));
        } else {
            return $this; // no map
        }
        foreach ($map->link as $data) {
            $alias = (string) $data->alias;
            $name = (string) $data->name;
            $sub = $url->createByAppendPath($alias,$name);
            $url->addChild($sub,$alias);
        }
        return $this;
    }

    /**
     * Gets a URL.
     *
     * @return string
     */
    function url()
    {
        $nav = $this->nav;
        $args = func_get_args();
        foreach ($args as $alias) {
            if (isset($nav->$alias)) {
                $nav = $nav->$alias;
            } else {
                $nav = clone($nav);
                $nav->appendPath($alias);
            }
        }
        return $nav->getUrl(new T_Filter_Xhtml());
    }

    /**
     * Add a breadcrumb stage.
     *
     * @param string $name
     * @param string|null $url  URL if available
     */
    function appendCrumb($name,$url=null)
    {
        if ($url) {
            $this->crumbs[$url] = $name;
        } else {
            $this->crumbs[] = $name;
        }
        return $this;
    }

    /**
     * Gets the breadcrumbs
     *
     * @return array   breadcrumbs as array(url=>name)
     */
    function getCrumbs()
    {
        return $this->crumbs;
    }

}
