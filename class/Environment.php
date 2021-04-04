<?php

namespace Deljdlx\Deploy;


class Environment
{

    protected $local;
    protected $name;
    protected $host;

    protected $hostName = '';
    protected $user = '';
    protected $identityFile = '';

    protected $variables = [];

    public function __construct($name, $local = false)
    {
        $this->name = $name;
        $this->local = $local;
        if(!$this->local) {
            $this->host = \Deployer\host($name);
        }
        else {
            $this->host = \Deployer\localhost($name);
        }

        $this->initialize();
    }


    public function initialize()
    {
        $this
            // [Optional] Allocate tty for git clone. Default value is false.
            ->set('git_tty', true)
            ->set('allow_anonymous_stats', false)
        ;
        return $this;
    }


    public function get($variableName)
    {
        if(array_key_exists($variableName, $this->variables)) {
            return $this->variables[$variableName];
        }
        else {
            throw new \Exception('Variable "' . $variableName . '" does not exist');
        }

    }

    public function set($variableName, $value)
    {
        $this->variables[$variableName] = $value;
        $this->host->set($variableName, $value);
        return $this;
    }

    public function hostname($name)
    {
        $this->hostName = $name;
        $this->host->hostname($name);
        return $this;
    }

    public function user($name) {
        $this->user = $name;
        $this->host->user($name);
        return $this;
    }

    public function identityFile($file)
    {
        $this->identityFile = $file;
        $this->host->identityFile($file);
        return $this;
    }


    public function __call($method, $arguments)
    {
        return call_user_func_array([$this, $method], $arguments);
    }

    public function enableSudo()
    {
        $this
            ->set('clear_use_sudo', true)
            ->set('writable_use_sudo', true)
        ;

        return $this;
    }
}

