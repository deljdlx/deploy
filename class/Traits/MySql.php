<?php

namespace Deljdlx\Deploy\Traits;

Trait MySql
{

    public function registerMysqlTrait()
    {

    }


    public function databaseExists($host, $user, $password, $database)
    {
        $query = "
            SELECT SCHEMA_NAME
                FROM INFORMATION_SCHEMA.SCHEMATA
            WHERE SCHEMA_NAME = '{$database}'
        ";

        $answer = $this->bddQuery($host, $user, $password, $database, $query);


        if($answer == $database) {
            return true;
        }
        else {
            return false;
        }



        return $answer;
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
        $answer = $this->run('mysql -h'. $host .' -u' . $user . ' -p' . $password . ' --execute="' . $query . '"  --skip-column-names', [
            //'tty' => true
        ]);

        return $answer;
    }
}