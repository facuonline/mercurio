    <meta charset="utf8">
    <?php
    require '../vendor/autoload.php';
    require 'autoload.php';

    \Mercurio\App::setApp([
        'KEY' => 'ñlaskdj',
        'URL' => 'http://localhost/mercurio/'
    ], [
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);

    $user = new \Mercurio\App\User;

    \Mercurio\Utils\Form::submit('login', function($data) use (&$user) {
        $user->login($data['username'], $data['password'], function() use (&$user) {
            echo "HELLO";
        });
    });

    \Mercurio\Utils\Form::submit('logout', function() use (&$user) {
        $user->logout(function() {
            echo "BYE";
        });
    });

    \Mercurio\Utils\Form::submit('modify', function($data, $files) use (&$user) {
        $user->set($data);
        $user->setImg($files['img'], 600, 600);
    }, function () {
        echo "SPAM";
    });

    \Mercurio\Utils\Form::new('login'); ?>
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button type="submit">Login</button>
    </form>

    <?php \Mercurio\Utils\Form::new('logout'); ?>
        <button type="submit">Logout</button>
    </form>

    <?php \Mercurio\Utils\Form::new('modify', 'POST', ['enctype' => 'multipart/form-data']); ?>
        <input type="text" name="nickname" value="<?php echo $user->getNickname(); ?>" placeholder="Nickname">
        <input type="file" name="img">
        <button type="submit">Update</button>
    </form>

    <img src="<?php echo $user->getImg(); ?>">