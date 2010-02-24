<?php
/**
 * Contains the T_Code_Sort class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Performs a topological sort to get classes and their dependencies in the right order.
 *
 * @see http://blog.metafoundry.com/2007/09/topological-sort-in-php.html
 * @package reflection
 */
class T_Code_Sort extends T_Filter_Skeleton
{

    /**
     * Creates a standard node.
     *
     * @param string $name
     * @return stdClass  node
     */
    protected function getNode($name)
    {
        $node = new stdClass();
        $node->name = $name;
        $node->children = array();
        $node->parents = array();
        return $node;
    }

    /**
     * Sorts an array of code objects.
     *
     * @param T_Code_Php[] $code
     * @return T_Code_Php[]  sorted list
     */
    protected function doTransform($code)
    {
        // build a dependency tree: an array where
        //  index => (depends on) index

        $c_lkup = array();
        foreach ($code as $key => $c) {
            foreach ($c->getClasses() as $class) {
                $c_lkup[$class] = $key;
            }
        }
        $deps = array();
        foreach ($code as $key => $c) {
            foreach ($c->getDependencies() as $d) {
                if (isset($c_lkup[$d])) {  // ignore missing dependencies
                    $deps[] = array($key=>$c_lkup[$d]);
                }
            }
        }

        // turn dependency pairs into a a double-linked node tree
        $nodes = array();
        foreach($deps as $key => $dpair) {
            list($module,$dependency) = each($dpair);
            if (!isset($nodes[$module]))
                $nodes[$module] = $this->getNode($module);
            if (!isset($nodes[$dependency]))
                $nodes[$dependency] = $this->getNode($dependency);
            if (!in_array($dependency,$nodes[$module]->children))
                $nodes[$module]->children[] = $dependency;
            if (!in_array($module,$nodes[$dependency]->parents))
                $nodes[$dependency]->parents[] = $module;
        }

        // get root nodes
        $root_nodes = array();
        foreach($nodes as $name => $node)
            if (count($node->parents)==0) $root_nodes[$name] = $node;

        // now perform a topological sort algorithm
        $sorted = array();
        while(count($nodes)>0) {

            // check for circular reference
            if (count($root_nodes)==0) {
                throw new RuntimeException('Circular dependencies in nodes to sort');
            }

            // remove a node from root_nodes
            $n = array_pop($root_nodes);
            $sorted[] = $n->name;

            // for each of its  children
            // queue the new node finally remove the original
            for($i=(count($n->children)-1);$i>=0;$i--) {
                $childnode = $n->children[$i];
                // remove the link from this node to its
                // children ($nodes[$n->name]->children[$i]) AND
                // remove the link from each child to this
                // parent ($nodes[$childnode]->parents[?]) THEN
                // remove this child from this node
                unset($nodes[$n->name]->children[$i]);
                $parent_position = array_search($n->name,$nodes[$childnode]->parents);
                unset($nodes[$childnode]->parents[$parent_position]);
                // check if this child has other parents
                // if not, add it to the root nodes list
                if (!count($nodes[$childnode]->parents)) array_push($root_nodes,$nodes[$childnode]);
            }

            // nodes.Remove(n);
            unset($nodes[$n->name]);
        }

        // dependencies must come first
        $sorted = array_reverse($sorted);

        // make sure we add back in any that don't have any dependencies at all
        foreach (array_keys($code) as $key) {
            if (!in_array($key,$sorted)) array_unshift($sorted,$key);
        }
        // now replace code objects back into sorted list
        foreach ($sorted as &$val) {
            $val = $code[$val];
        }
        unset($val);

        return $sorted;
    }

}