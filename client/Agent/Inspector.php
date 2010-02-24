<?php
/**
 * An container for various rules to examine the client agent.
 *
 * <?php
 * $agent = new T_Agent_Inspector;
 * $agent->addParser(new MyMobileDetector1)
 *       ->addParser(new MyMobileDetector2);
 *
 * // get best answer (might be tentative)..
 * $mob = $agent->isMobile();
 *
 * // get answers that is at least likely, or none at all
 * $mob = $agent->atLeast(T_Agent_Answer::LIKELY)
 *              ->isMobile();
 * ?>
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */
class T_Agent_Inspector
{

    protected $parsers = array();
    protected $min;

    /**
     * Specify minimum acceptable confidence level.
     */
    function __construct($min=null)
    {
        $this->min = $min;
    }

    /**
     * Add a parser to the inspector.
     *
     * @param T_Agent_Parser
     * @return T_Agent_Inspector
     */
    function addParser(T_Agent_Parser $parser)
    {
        $this->parsers[] = $parser;
        return $this;
    }

    /**
     * Specify a minimum confidence level for the next fluent call.
     *
     * Note that this call must be used in a fluent context as it returns
     * a new instance of the agent inspector with the minimum confidence
     * threshold.
     * <?php
     * $is_mob = $agent->atLeast(T_Agent_Answer::LIKELY)
     *                 ->isMobile();
     * ?>
     *
     * @param int $confidence  confidence level
     * @return T_Agent_Level  agenet with same parsers and min confidence
     */
    function atLeast($confidence)
    {
        $agent = new self($confidence);
        foreach ($this->parsers as $p) $agent->addParser($p);
        return $agent;
    }

    /**
     * Catch any query method calls and proxy to the parsers.
     *
     * @return mixed
     */
    function __call($name,$args)
    {
        // build a list of all the answers from any parsers with this method
        $ans = array();
        foreach ($this->parsers as $p) {
            if (!method_exists($p,$name)) continue;
            $a = call_user_func_array(array($p,$name),$args);
            if (!is_null($a) && $a->getConfidence()>=$this->min) $ans[] = $a;
        }

        // simple cases
        if (count($ans)==0) return null;
        if (count($ans)==1) return $ans[0]->getAnswer();

        // multiple answers try to filter on level of certainty...
        $max = null;
        foreach ($ans as $a) if (($c=$a->getConfidence())>$max) $max=$c;
        foreach (array_keys($ans) as $k) {
            if ($ans[$k]->getConfidence()<$max) unset($ans[$k]);
        }
        if (count($ans)==1) return _end($ans)->getAnswer();

        // multiple answers of similar certainty, use most popular
        // answer, otherwise fallback to first
        $vals = array();
        $pop = array();
        foreach ($ans as $a) {
            $v = $a->getAnswer();
            $sig = md5(serialize($a));
            if (isset($pop[$sig])) {
                $pop[$sig]++;
            } else {
                $pop[$sig] = 1;
            }
            $vals[$sig] = $v;
        }
        asort($pop,SORT_NUMERIC);
        $pop = array_reverse($pop);
        reset($pop);
        return $vals[key($pop)];
    }

}