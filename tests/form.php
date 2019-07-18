<?php
require '../vendor/autoload.php';
require 'autoload.php';

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

    Form::submit('myForm', function ($data, $files) {
        # This was a human generated submission, 
        # you can access its filtered $data
        # and the raw $files array
        print_r($data);
        # you can also filter specific fields by their names
        echo Form::get('example', 'string'); 
        # This method can be accessed globally without using ::submit
    }, function ($data, $session) {
        # This submission was more than likely not made by a human
        # you can also access its filtered $data, without files
        # and you can read the $session info
    });
    
    Form::new('myForm', 'POST'); ?>
        <input type="text" name="example" placeholder="Fill me.">
        <p>Check the source code in your browser to see more details about this form.</p>
        <button type="submit">Submit button</button>
    </form>
