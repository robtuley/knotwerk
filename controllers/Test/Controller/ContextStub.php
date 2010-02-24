<?php
class T_Test_Controller_ContextStub implements T_Controller_Context,T_Test_Stub
{

    protected $coerce_scheme = null;
    protected $url;
    protected $subspace;
    protected $delegate_to = false;
    protected $env;

    function __construct(T_Environment $env,T_Url $url,array $subspace,$delegated=false)
    {
        $this->env = $env;
        $this->url = $url;
        $this->subspace = $subspace;
        $this->delegated = $delegated;
    }

    function getUrl()
    {
        return $this->url;
    }

    function getSubspace()
    {
        return $this->subspace;
    }

    function setSubspace(array $subspace)
    {
        $this->subspace = $subspace;
    }

    function coerceScheme($scheme)
    {
        $this->coerce_scheme = $scheme;
        return $this;
    }

    function getCoerceScheme()
    {
        return $this->coerce_scheme;
    }

    function isDelegated()
    {
        return $this->delegate_to!==false;
    }

    function delegate($name)
    {
        $this->delegate_to = $name;
        return $this;
    }

    function like($class,array $args=array())
    {
        return $this->env->like($class,$args);
    }

    function willUse($class,$alias=null)
    {
        $this->env->willUse($class,$alias);
        return $this;
    }

    function find($query,$type=null)
    {
        return $this->env->find($query,$type);
    }

    function addRule(T_Find_Rule $rule)
    {
        $this->env->addRule($rule);
        return $this;
    }

    function input($name)
    {
        return $this->env->input($name);
    }

    function setAppRoot($root)
    {
        $this->env->setAppRoot($root);
    }

    function getAppRoot()
    {
        return $this->env->getAppRoot();
    }

    function getRequestUrl()
    {
        return $this->env->getRequestUrl();
    }

    function getMethod($filter=null)
    {
        return $this->env->getMethod($filter);
    }

    function isMethod($method)
    {
        return $this->env->isMethod($method);
    }

    function isAjax()
    {
        return $this->env->isAjax();
    }

}
