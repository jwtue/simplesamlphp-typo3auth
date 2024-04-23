<?php

declare(strict_types=1);

namespace SimpleSAML\Module\typo3auth\Auth\Source;

use \PDO;

class Typo3 extends \SimpleSAML\Module\core\Auth\UserPassBase {

    private $dsn;

    /* The database username, password & options. */
    private $username;
    private $password;
    private $options;

    public function __construct(array $info, array $config)
    {
        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);

        if (!is_string($config['dsn'])) {
            throw new Exception('Missing or invalid dsn option in config.');
        }
        $this->dsn = $config['dsn'];
        if (!is_string($config['username'])) {
            throw new Exception('Missing or invalid username option in config.');
        }
        $this->username = $config['username'];
        if (!is_string($config['password'])) {
            throw new Exception('Missing or invalid password option in config.');
        }
        $this->password = $config['password'];
        if (isset($config['options'])) {
            if (!is_array($config['options'])) {
                throw new Exception('Missing or invalid options option in config.');
            }
            $this->options = $config['options'];
        }
    }

    protected function login(string $username, string $password): array {

        /* Connect to the database. */
        $db = new PDO($this->dsn, $this->username, $this->password, $this->options);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /* Ensure that we are operating with UTF-8 encoding.
         * This command is for MySQL. Other databases may need different commands.
         */
        $db->exec("SET NAMES 'utf8'");

        /* With PDO we use prepared statements. This saves us from having to escape
         * the username in the database query.
         */
        $st = $db->prepare('SELECT uid, username, password, usergroup, name, email FROM fe_users WHERE username=:username AND '
            .'disable = 0 AND deleted = 0 AND '
            .'(starttime = 0 OR starttime < UNIX_TIMESTAMP(NOW())) AND'
            .'(endtime = 0 OR endtime > UNIX_TIMESTAMP(NOW()))'
            );

        if (!$st->execute(['username' => $username])) {
            throw new Exception('Failed to query database for user.');
        }

        /* Retrieve the row from the database. */
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            /* User not found. */
            \SimpleSAML\Logger::warning('Typo3Auth: Could not find user ' . var_export($username, TRUE) . '.');
            throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
        }

        /* Check the password. */
        if (!password_verify($password, $row['password'])) {
            /* Invalid password. */
            \SimpleSAML\Logger::warning('Typo3Auth: Wrong password for user ' . var_export($username, TRUE) . '.');
            throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
        }

        $groups = explode(",", $row["usergroup"]);
        $in = str_repeat('?,', count($groups)-1).'?';
        $st = $db->prepare("SELECT uid, title FROM fe_groups WHERE uid IN ($in) AND hidden = 0 AND deleted = 0");
        $st->execute($groups);
        $grouprows = $st->fetchAll();
        $groupnames = array_map(fn($val) => $val['title'],  $grouprows);

        /* Create the attribute array of the user. */
        $attributes = [
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn' => [$row['username']],
            'http://schemas.xmlsoap.org/claims/CommonName' => [$row['name']],
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress' => [$row['email']],
            'http://schemas.xmlsoap.org/claims/Group' => $groupnames,
        ];

        /* Return the attributes. */
        return $attributes;
    }
}