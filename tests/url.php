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
echo \Mercurio\Utils\URL::slugify('This string is not valid for an url but it should appear as one in your browser', true);
echo "<pre>";
print_r(\Mercurio\Utils\URL::getUrlParams());
$referrer = \Mercurio\Utils\URL::getUrlParams()['Referrer'];
if ($referrer) {
    echo "Referrer is '$referrer'";
} else {
    echo "Referrer is not specified\n";
}
echo "\nAll referrers:\n";
print_r(\Mercurio\Utils\URL::getReferrerList());
echo "</pre>\nTry passing it params in the URL.";
