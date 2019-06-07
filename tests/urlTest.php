<?php require '../vendor/autoload.php';
require '../src/classes/app.class.php';
require '../src/classes/app/database.class.php';
require '../src/classes/utils/url.class.php';

\Mercurio\App::setApp([
    'KEY' => '43bb4fa713655013c0fb89c36d03dc9111ea4f5513a75b1c78fb5bc5653f2c1f',
    'URL' => 'http://localhost/mercurio/',
], [
    'HOST' => 'localhost',
    'USER' => 'root',
    'PASS' => '',
    'NAME' => 'mercurio'
]);

echo "<pre>";
print_r(\Mercurio\Utils\URL::getUrlParams());