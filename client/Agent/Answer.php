<?php
/**
 * An answer to a client agent query.
 *
 * This class is designed to be used in conjunction with the
 * T_Agent_Inspection class and various agent parsers. It
 * encapsulates both an answer itself and a confidence level
 * in the answer.
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */
class T_Agent_Answer
{

    /**
     * Constants to define answer confidence.
     */
    const TENTATIVE = 1,
          LIKELY = 2,
          DEFINITE = 4;

    protected $answer;
    protected $confidence;

    /**
     * Create answer.
     *
     * @param mixed $answer
     * @param int $confidence  answer confidence (tentative,definite,etc)
     */
    function __construct($answer,$confidence)
    {
        $this->answer = $answer;
        $this->confidence = $confidence;
    }

    /**
     * Gets the query answer.
     *
     * @return mixed
     */
    function getAnswer($filter=null)
    {
        return _transform($this->answer,$filter);
    }

    /**
     * Gets the answer confidence.
     *
     * @return int
     */
    function getConfidence($filter=null)
    {
        return _transform($this->confidence,$filter);
    }

}