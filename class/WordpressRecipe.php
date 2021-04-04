<?php

namespace Deljdlx\Deploy;

class WordpressRecipe extends Recipe
{
    use Traits\MySql;

    public function initialize()
    {

        parent::initialize();
        $this->setTask('generateConfiguration', function() {
            $template = "<?php

define( 'WP_USE_THEMES', " . $this->get('WP_USE_THEMES', true) . " );
define( 'WP_ENVIRONMENT_TYPE', '" . $this->get('WP_ENVIRONMENT_TYPE') . "');
define( 'WP_DEBUG', " . $this->get('WP_DEBUG', true) . " );

define( 'DB_NAME', '" . $this->get('DB_NAME') . "' );
define( 'DB_USER', '" . $this->get('DB_USER') . "' );
define( 'DB_PASSWORD', '" . $this->get('DB_PASSWORD') . "' );
define( 'DB_HOST', '" . $this->get('DB_HOST') . "' );
define( 'DB_CHARSET', '" . $this->get('DB_CHARSET') . "' );
define( 'DB_COLLATE', '" . $this->get('DB_COLLATE') . "' );
\$table_prefix = '" . $this->get('DB_TABLE_PREFIX') . "';

define('WP_HOME', rtrim ( '" . $this->get('WP_HOME') . "', '/' ));
define('WP_SITEURL', WP_HOME . '" . $this->get('WP_SOURCE_FOLDER') . "');

define('JWT_AUTH_SECRET_KEY', '" . $this->get('JWT_AUTH_SECRET_KEY') . "');
define('JWT_AUTH_CORS_ENABLE', " . $this->get('JWT_AUTH_CORS_ENABLE', true) . ");

define('FS_METHOD','" . $this->get('FS_METHOD') . "');

define( 'AUTH_KEY',         '" . $this->get('AUTH_KEY') . "' );
define( 'SECURE_AUTH_KEY',  '" . $this->get('SECURE_AUTH_KEY') . "' );
define( 'LOGGED_IN_KEY',    '" . $this->get('LOGGED_IN_KEY') . "' );
define( 'NONCE_KEY',        '" . $this->get('NONCE_KEY') . "' );
define( 'AUTH_SALT',        '" . $this->get('AUTH_SALT') . "' );
define( 'SECURE_AUTH_SALT', '" . $this->get('SECURE_AUTH_SALT') . "' );
define( 'LOGGED_IN_SALT',   '" . $this->get('LOGGED_IN_SALT') . "' );
define( 'NONCE_SALT',       '" . $this->get('NONCE_SALT') . "' );
";

            $this->write('{{site_filepath}}/configuration-current.php', $template);

        });

        return $this;
    }


    public function registerTasks()
    {
        parent::registerTasks();
        $this->registerMysqlTrait();

        $this->setTask('installRequirements', function() {
            return $this->installRequirements();
        });

        $this->setTask('dropTables', function() {
            return $this->dropTables(
                $this->get('DB_HOST'),
                $this->get('DB_USER'),
                $this->get('DB_PASSWORD'),
                $this->get('DB_NAME'),
                $this->get('DB_TABLE_PREFIX')
            );
        });

        $this->setTask('installWordpress', function() {
            return $this->installWordpress(
                $this->get('site_filepath'),
                $this->get('WP_HOME'),
                $this->get('SITE_NAME'),
                $this->get('BO_USER'),
                $this->get('BO_PASSWORD'),
                $this->get('BO_EMAIL')
            );
        });

        $this->setTask('chmod', function() {
            return $this->chmod();
        });

        $this->setTask('buildHtaccess', function() {
            return $this->buildHtaccess();
        });

        $this->setTask('displayInformations', function() {
            return $this->displayInformations();
        });

        $this->setTask('activatePlugins', function() {
            return $this->activatePlugins();
        });
    }


    public function activatePlugins()
    {
        $this->cd('{{site_filepath}}');
        $this->run('composer run activate-plugins');
        return $this;
    }


    public function displayInformations()
    {
        $this->echo('Wordpress installed : ' . $this->get('WP_HOME'));
        $this->echo('Backoffice : ' . $this->get('WP_HOME') .'/' . $this->get('WP_SOURCE_FOLDER') . '/wp-admin');
    }

    public function installWordpress($path, $home, $name, $login, $password, $email)
    {
        $this->cd($path);
        $this->run('wp core install --url="' . $home . '" --title="' . $name . '" --admin_user="' . $login . '" --admin_password="' . $password . '" --admin_email="' . $email . '" --skip-email;');
    }

    public function chmod()
    {
        $this->cd('{{site_filepath}}');
        $this->run('composer run chmod');
        $this->run('sudo chmod -R 775 wp-content');
        return $this;
    }

    public function buildHtaccess()
    {
        $this->cd('{{site_filepath}}');
        $this->run('composer run activate-htaccess');
        $this->run ("echo 'RewriteCond %{HTTP:Authorization} ^(.*)' >> ./.htaccess");
        $this->run ("echo 'RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]' >> ./.htaccess");
        $this->run ("echo 'SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1' >> ./.htaccess");
        return $this;
    }


    public function installRequirements()
    {
        if(!$this->isFile('/usr/local/bin/wp')) {
            $this->run('curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp');
        }
        if($this->isFile('/usr/local/bin/composer')) {
            $this->run('cd /tmp && php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');" && php composer-setup.php --quiet && sudo mv composer.phar /usr/local/bin/composer');
        }
    }

    public function dropTables($host, $user, $password, $database, $tablePrefix)
    {
        $this->run('mysql -h'. $host .' -u' . $user . ' -p' . $password . ' --execute="' .
            'use '.$database.';' .
            'DROP TABLE `' . $tablePrefix . 'term_relationships`;'.
            'DROP TABLE `' . $tablePrefix . 'terms`;'.
            'DROP TABLE `' . $tablePrefix . 'termmeta`;'.
            'DROP TABLE `' . $tablePrefix . 'users`;'.
            'DROP TABLE `' . $tablePrefix . 'usermeta`;'.
            'DROP TABLE `' . $tablePrefix . 'term_taxonomy`;'.
            'DROP TABLE `' . $tablePrefix . 'links`;'.
            'DROP TABLE `' . $tablePrefix . 'comments`;'.
            'DROP TABLE `' . $tablePrefix . 'commentmeta`;'.
            'DROP TABLE `' . $tablePrefix . 'posts`;'.
            'DROP TABLE `' . $tablePrefix . 'postmeta`;'.
            'DROP TABLE `' . $tablePrefix . 'options`;'.
        '"');
    }
}
