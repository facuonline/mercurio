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

    $user = new \Mercurio\App\User;
    $user->get('verano'); 

    ?>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }
        header {
            max-width: 80px;
            width: 100%;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #f99a0550;
        }
        header img {
            width: 60px;
            border-radius: 100%;
        }
        header nav ul {
            padding: 0;
            list-style: none;
            text-align: right;
        }
        header nav ul li {
            padding: 1em 10px;
        }
        header nav ul li:hover{
            background: #fff;
            color: #333;
        }
        .profile {
            max-width: calc(900px - 2em);
            margin: auto;
            padding: 1em;
        }
        .profile img {
            max-width: 200px;
            margin: 1em;
            border-radius: 100%;
            float: left;
        }
        .profile .info {
            display: inline-block;
        }
        .profile .info, .profile .info h1{
            font-weight: 100;
        }
    </style>
    <header>
        <nav>
            <ul>
                <li>
                <a href="<?php echo $user->getLink(); ?>">
                    <img src="<?php echo $user->getImg(); ?>">
                </a>
                </li>
                <li>Feed</li>
                <li>Portada</li>
                <li>Buscar</li>
            </ul>
        </nav>
    </header>
    <div class="profile">
        <img src="<?php echo $user->getImg(); ?>">
        <div class="info">
            <h1><?php echo $user->getHandle(); ?></h1>
            <span><?php echo $user->info['nickname']; ?></span>
            <?php echo $_SERVER['DOCUMENT_ROOT']; ?>
        </div>
    </div>
