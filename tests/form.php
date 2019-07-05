<?php
require '../vendor/autoload.php';
require '../src/classes/app.class.php';
require '../src/classes/app/database.class.php';
require '../src/classes/utils/url.class.php';
require '../src/classes/utils/form.class.php';
require '../src/classes/exception/usage.class.php';

// To run this test configure your APP url and DB, then open this file in your browser

\Mercurio\App::setApp([
    'KEY' => '43bb4fa713655013c0fb89c36d03dc9111ea4f5513a75b1c78fb5bc5653f2c1f',
    'URL' => 'http://localhost/mercurio/', 
], [
    'HOST' => 'localhost',
    'USER' => 'root',
    'PASS' => '',
    'NAME' => 'mercurio'
]); 
    use \Mercurio\Utils\Form;

    if (Form::spam('myForm')) {
        // Here you can run some script to punish spam bots
        echo "It's spam!";
    } else {
        // You can also filter non bot received data
        echo Form::get('example');
    };
    ?>

    <?php Form::new(['method' => 'POST', 'listener' => 'myForm']); ?>
        <input type="text" name="example" placeholder="Fill me.">
        <p>Check the source code in your browser to see more details about this form.</p>
        <button type="submit">Submit button</button>
    </form>

