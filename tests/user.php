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

    // Validate form
    if (!Form::spam('login')) {
        try {
            $user->login(
                Form::get('login_user'),
                Form::get('login_password')
            );
            echo "LOGIN SUCCESS";
        } catch (Mercurio\Exception $error) {
            echo "LOGIN ERROR";
        }
    }

    echo "<pre>";

    print_r(\Mercurio\Utils\Session::get());

    $user->get();

    echo $user->getHandle();

    ?>

    <?php Form::new('POST', 'login'); ?>
        <input type="text" name="login_user" placeholder="Username or email">
        <input type="password" name="login_password" placeholder="Password">
        <button type="submit">Login</button>
    <?php Form::end(); ?>