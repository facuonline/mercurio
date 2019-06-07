<?php require '../vendor/autoload.php';
require '../src/classes/app.class.php';
require '../src/classes/app/database.class.php';
require '../src/classes/utils/url.class.php';

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
echo \Mercurio\Utils\URL::slugify('este string no es válido para una url pero en tu navegador sí debería serlo', true);
echo "<pre>";
print_r(\Mercurio\Utils\URL::getUrlParams());
$referrer = \Mercurio\Utils\URL::getUrlParams()['Referrer'];
if ($referrer) {
    echo "Referrer is '$referrer'";
} else {
    echo "Referrer is not specified\n";
}
echo "</pre>\nTry passing it params in the URL.";