<?php

namespace Deljdlx\Deploy;

class Recipe
{

    protected $variables = [];
    protected $tasks = [];

    protected $environments = [];


    use Traits\Shell;
    use Traits\File;
    use Traits\Composer;




    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        $this->registerTasks();

    }

    protected function registerTasks()
    {
        $this->setTask('ls', function() {
            $this->ls($this->get('site_filepath'));
        });
    }



    public function parse($string)
    {
        return preg_replace_callback('`(\{\{(.*?)\}\})`', function($matches) {
            $variable = $matches[2];
            return $this->get($variable);
        }, $string);
    }


    public function setTask($name, $callback)
    {
        $this->tasks[$name] = $callback;
        \Deployer\task($name, function() use ($name) {
            call_user_func_array([$this, 'execute'], [$name]);
        });
        return $this;
    }

    public function execute($name, $parameters = [])
    {
        if(array_key_exists($name, $this->tasks)) {
            call_user_func_array($this->tasks[$name]->bindTo($this), $parameters);
            return $this;
        }
        else {
            throw new \Exception('Task "' . $name .'" does not exist');
        }
    }


    public function setEnvironment($name, $enviroment)
    {
        $this->environments[$name] = $enviroment;
        return $this;
    }


    public function get($variableName, $export = false)
    {
        if(array_key_exists($variableName, $this->variables)) {

            if($export) {
                return var_export($this->variables[$variableName], true);
            }
            else {
                return $this->variables[$variableName];
            }
        }
        elseif(($value = \Deployer\get($variableName)) !== null) {
            $this->set($variableName, $value);
            if($export) {
                return var_export($value, true);
            }
            else {
                return $value;
            }
        }
        else {
            throw new \Exception('Variable "' . $variableName . '" does not exist');
        }
    }

    public function set($variableName, $value)
    {
        $this->variables[$variableName] = $value;
        \Deployer\set($variableName, $value);
        return $this;
    }
}
