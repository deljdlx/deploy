<?php

namespace Deljdlx\Deploy\Traits;

trait Composer
{
    public function composerInstall($path)
    {
        if($this->isFile($path .'/composer.json')) {
            $this->cd($path);
            return $this->run('composer install', [
                'tty' => true
            ]);
        }

        return false;
    }
}
