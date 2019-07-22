    <meta charset="utf8">
    <?php
    require '../vendor/autoload.php';
    require 'autoload.php';

    \Mercurio\App::setApp([
        'KEY' => 'laksdfj',
        'URL' => 'http://localhost/mercurio/'
    ]);

    echo "<pre>";
    print_r(pathinfo(APP_CSRFPHP));