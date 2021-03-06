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


    public function ask($message, $default = null, $suggestedChoice = null)
    {
        $choice = \Deployer\ask($message, $default, $suggestedChoice);
        return $choice;
    }

    public function hostname()
    {
        return \Deployer\hostname();
    }


    protected function registerTasks()
    {
        $this->setTask('ls', function() {
            $this->ls($this->get('site_filepath'));
        });
    }


    public function generateGulpWatch($url, $path = null)
    {
        $template = "
        var gulp = require('gulp');
        var browserSync = require('browser-sync');


        gulp.task('default', function() {
          browserSync({
            proxy: '{$url}'
          });

          gulp.watch('**/*.php').on('change', function () {
            browserSync.reload();
          });
        });
        ";

        if($path === null) {
            $path = getcwd();
        }

        file_put_contents($path . '/gulpfile.js', $template);

    }


    public function replaceInFile($file, $from, $to)
    {
        $file = $this->parse($file);

        $this->run(
            'php -r \''.
            '$buffer = file_get_contents("'. $file . '");'.
            '$buffer = str_replace("' . str_replace('"', '\"', $from) . '", "' . str_replace('"', '\"', $to) . '", $buffer);' .
            'file_put_contents("' . $file . '", $buffer);' .
            '\''
        ) ;
        return $this;
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
        $this->variables[$variableName] = $this->parse($value);
        \Deployer\set($variableName, $value);
        return $this;
    }
}
