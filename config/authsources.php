<?php

$config = [

    /* ... */

    /*    
        Your Typo3 database settings might look something like this:
        
        'Connections' => [
            'Default' => [
                'charset' => 'utf8',
                'dbname' => 'my_db_name',
                'driver' => 'pdo_mysql',
                'driverOptions' => [
                    1009 => '/path/to/certificate.pem',
                ],
                'host' => 'localhost',
                'user' => 'my_db_user',
                'password' => 'my_db_password',
                'port' => 3306,
            ],
        ],
        
        Copy the values to the SimpleSAMLphp config like below:
    */


    'typo3' => [
        'typo3auth:Typo3',
        'dsn' => 'mysql:host=localhost;dbname=my_db_name;charset=UTF8;port=3306',
        'username' => 'my_db_user',
        'password' => 'my_db_password',
        'options' => [
            1009 => '/path/to/certificate.pem'
        ],
        'core:loginpage_links' => [ // Optional: add link to "forgot password" from typo3
            [
                'href' => 'https://example.com/login?tx_felogin_login%5Baction%5D=recovery&tx_felogin_login%5Bcontroller%5D=PasswordRecovery&cHash=c4a71b337afdbd61afdd6fc1de5d8a59',
                'text' => 'Forgot password?',
            ],
        ],
    ],

    /* ... */
];
