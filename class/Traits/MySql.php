<?php

namespace Deljdlx\Deploy\Traits;

Trait MySql
{

    public function registerMysqlTrait()
    {
        $this->setTask('createBDD', function() {
            $this->createBDD(
                $this->get('DB_HOST'),
                $this->get('DB_USER'),
                $this->get('DB_PASSWORD'),
                $this->get('DB_NAME')
            );
        });


        $this->setTask('dropBDD', function() {
            $this->dropBDD(
                $this->get('DB_HOST'),
                $this->get('DB_USER'),
                $this->get('DB_PASSWORD'),
                $this->get('DB_NAME')
            );
        });
    }


    public function getBDDdump($host, $user, $password, $database, $output)
    {
        $this->echo('Exporting ' . $database . '@' . $host . ' to ' . $database . '.sql');
        $this->run('mysqldump -h' . $host . ' -u' . $user . ' -p' . $password . ' ' . $database . ' > ' . $output);

        return $this;
    }


    public function createBDD($host, $user, $password, $database)
    {
        $this->bddQuery($host, $user, $password, $database, 'CREATE DATABASE ' . $database . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        return  $this;
    }

    public function dropBDD($host, $user, $password, $database)
    {
        $this->bddQuery($host, $user, $password, $database, 'DROP DATABASE ' . $database);
        return $this;
    }

    public function bddQuery($host, $user, $password, $database, $query)
    {
        print_r($query);
        $this->run('mysql -h'. $host .' -u' . $user . ' -p' . $password . ' --execute="' . $query . '"');
        return $this;
    }
}