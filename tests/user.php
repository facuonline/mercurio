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

    $user->get('verano');

    echo "<pre>";

    // Validate form against spam and sanitize
    \Mercurio\Utils\Form::submit('login', function($post, $files) use (&$user) {
        # You can perform your desired actions here
        # $data will return array with sanitized form data from submission
        # $files will return $_FILES array, raw (you can handle them via the bulletproof class)
        $user->setImg('user_img', 'static', 600, 600);
        print_r($user->info);
    }, function ($data, $session) {
        # You can perform aditional antispam here,
        # by default Mercurio will only ignore their submissions
        # $data will return array with sanitized form data from submission
        # $session will return session data
        print_r($data);
        print_r($session);
    });

    \Mercurio\Utils\Form::new('login', 'POST', ['enctype' => 'multipart/form-data']); ?>
        <input type="file" name="user_img" accept="image/*">
        <button type="submit">Upload</button>
    <?php \Mercurio\Utils\Form::end(); ?>