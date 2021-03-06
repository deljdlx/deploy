<?php

namespace Deljdlx\Deploy\Traits;

trait Shell
{


    public function ls($path)
    {
        $this->cd($path);
        $this->run('ls -al', [
            'tty' => true
        ]);
    }


    public function echo($string)
    {
        \Deployer\writeln($string);
        return $this;
    }


    public function cd($path)
    {
        \Deployer\cd($path);
        return $this;
    }

    public function test($test)
    {
        return \Deployer\test($test);
    }


    public function run($command, $options = [])
    {
        return \Deployer\run($command, $options);
    }


    public function isFile($path)
    {
        return $this->test('[ -f "' . $path . '" ]');
    }

    public function isDir($path)
    {
        return $this->test('[ -d "' . $path . '" ]');
    }


}
