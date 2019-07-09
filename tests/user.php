<?php
require '../vendor/autoload.php';
require '../src/app.class.php';
require '../src/exception/user/existinghandle.class.php';
require '../src/app/database.class.php';
require '../src/utils/system.class.php';
require '../src/utils/url.class.php';
require '../src/utils/id.class.php';
require '../src/utils/form.class.php';
require '../src/utils/session.class.php';
require '../src/app/user.class.php';

use \Mercurio\App;
use \Mercurio\Utils\Form;
use \Mercurio\App\User;


    // Start our app
    App::setApp([
        'KEY' => '43bb4fa713655013c0fb89c36d03dc9111ea4f5513a75b1c78fb5bc5653f2c1f',
        'URL' => 'http://localhost/mercurio/', 
    ], [
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);

    // Start user object
    $user = new User;

    echo "<pre>";

    // Validate form against spam and sanitize
    Form::submit('login', function($data) use (&$user) {
        # You can perform your desired actions here
        # $data will return array with sanitized form data from submission
        try {
            $user->login(
                $data['login_user'], 
                $data['login_password']
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
    
    $user->getSession(function($data) {
        print_r($data);
    });

    if (isset($_GET['logout'])) {
        $user->logout();
    }

    ?>

    <?php Form::new('login', 'POST'); ?>
        <input type="text" name="login_user" placeholder="Username or email">
        <input type="password" name="login_password" placeholder="Password">
        <button type="submit">Login</button>
    <?php Form::end(); ?>