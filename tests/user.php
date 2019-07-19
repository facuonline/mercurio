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
    $user->get('verano');

    \Mercurio\Utils\Form::submit('userForm', function ($data, $image) use (&$user) {
        $user->setImg('userImg', 600, 600);
    });

    \Mercurio\Utils\Form::new('userForm', 'POST', ['enctype' => 'multipart/form-data']); ?>
        <input type="file" name="userImg">
        <button type="submit">Upload</button>
    </form>

    <img src="<?php echo $user->getImg(); ?>">
    <a href="<?php echo $user->getLink(); ?>"><?php echo $user->getLink(); ?></a>