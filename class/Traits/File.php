<?php

namespace Deljdlx\Deploy\Traits;

Trait File
{

    public function write($output, $content)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'deploy_');
        file_put_contents($tempFile, $content);
        $this->upload($tempFile, $output);
        unlink($tempFile);
    }

    public function replaceInFile($file, $from, $to, $destination = null)
    {
        $file = $this->parse($file);

        if($destination === null) {
            $destination = $file;
        }
        else {
            $destination = $this->parse($destination);
        }

        if(!is_file($file)) {
            $this->download($file, $file);
        }

        $content = file_get_contents($file);
        $content = str_replace($from, $to, $content);

        $this->write($destination, $content);
        return $this;
    }


    public function download($from, $to)
    {
        \Deployer\download($from, $to);
        return $this;
    }

    public function upload($from, $to)
    {
        \Deployer\upload($from, $to);
        return $this;
    }

    public function mkdir($path)
    {
        $this->run('mkdir ' . $path);
        return $this;
    }


}