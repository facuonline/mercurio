<?php
require '../vendor/autoload.php';
require 'autoload.php';

    // Start our app
    \Mercurio\App::setApp([
        'KEY' => '43bb4fa713655013c0fb89c36d03dc9111ea4f5513a75b1c78fb5bc5653f2c1f',
        'URL' => 'http://localhost/mercurio/', 
    ], [
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);

    // Start user object
    $user = new \Mercurio\App\User;

    echo "<pre>";

    // Validate form against spam and sanitize
    \Mercurio\Utils\Form::submit('login', function($data) use (&$user) {
        # You can perform your desired actions here
        # $data will return array with sanitized form data from submission
        try {
            $user->login(
                $data['login_user'], 
                $data['login_password'],
            );
        } catch (\Mercurio\Exception\User $error) {
            echo $error->getCode();
        }
    }, function ($data) {
        # You can perform aditional antispam here,
        # by default Mercurio will only ignore their submissions
        # $data will return array with sanitized form data from submission and session data
        print_r($data);
    }); 

    $user->get('vito', function($user) {
        echo $user['id'];
    }, function () {
        echo "NINGUN USUARIO CON ESE NOMBRE";
    });

    ?>