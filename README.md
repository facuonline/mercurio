# Mercurio
Courier. Not CMS. **Still in development**.

Comprehensive library to help you develop safer, better web apps in PHP.

[Example App](#your-first-app) /
[Contributing](#contributing) /
[TODOs](#TODOs)

## Installation
```
    composer require mercurio/mercurio
```
## Usage
Once installed you must start Mercurio with some basic configurations.

```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/mercurio/'
    ]);
```

Alternatively if you have a database:
```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/mercurio/'
    ], [
        // check catfan/medoo for a complete list of supported database types
        'TYPE' => 'mysql', 
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);
```

This will prepare your environment to work with Mercurio. To work with the `App` classes it's necessary that you have a SQL database, either way you'll only be able to use a basic set of Mercurio tools (i.e just `Utils`).
>You can obtain a random, safe key using **`Mercurio\App::randomKey()`** (hardcoding the value in the array, the app key needs to remain consistent trough the App's life)

### Utils and App
Mercurio is divided in two sets of classes. 

**`Utils`** is a list of microservices both for the system and for the developer. These classes are mostly static so their methods can be accessed on the go and for the most part don't require a database to work. Their importance varies, sometimes they'll be at the very core of our App and sometimes they won't be called once.

**`App`** classes are the main App services model. They encapsulate Mercurio components and their behaviour under simple and easy to use objects and methods.

>**`Utils`** are part of the **Controller** and **`App`** are the **Model** in the Model-View-Controller design pattern. All there is left to you after glueing these bricks as you wish, is to develop a nice **View** template.

There are also many custom **`Exceptions`**, but they serve mainly to provide verbose errors to developers, like `\Usage`, or to add better, extended, exception behaviour out of the box, like `User\*`. As Exceptions they don't provide any functionability *per se*.

### **Your first app**
Following is only an example App. Mercurio can be used in many different ways and for many different purposes.

#### Folder structure
After installing mercurio your project folder sure looks something like this:

    | my-mercurio-app
        | vendor
        composer.json
        composer.lock

Our example app will now include an index.php file where we'll start our Mercurio app as seen above. And a /views/ folder where we will be storing our different view templates.

#### Routing
We will be using [`AltoRouter`](https://github.com/dannyvankooten/AltoRouter) to sort user requests, serve the correct view template and in the end, just organize our app out. This package comes bundled with Mercurio.

##### URL masking

```php
    // Apache only
    \Mercurio\Utils\Htaccess::setMasking();
    // For nginx you'll need to manually configure a rewrite rule
```
This method will set up, if possible, URL masking via apache's mod_rewrite. Just write it in your **index.php** and run the page in your browser. Nothing will happen in your screen but Mercurio will silently set up the URL masking in your app folder.

After running it you can delete that line to avoid calling the same method over and over. It will not perform a new masking, but it will consume some resources on every request just by having to check if it needs to do the masking.

**It is necessary** that you run this method in order for AltoRouter to work. If you use Nginx you'll need to update your **ngingx.conf** file like so:
```
try_files $uri /index.php;
```

##### Templating

[AltoRouter](https://github.com/dannyvankooten/AltoRouter) will easily help us at listening for specific requests and respond to them:
```php
    $router = new AltoRouter;
    // We set the app basepath in case it's in a subdirectory
    // Do not add a forward slash at the end
    $router->setBasePath('/my-mercurio-app');

    $router->map('GET', '/', function() {
        require 'views/home.php';
    });

    $router->map('GET', '/user/[i:id]/', function($request) {
        require 'views/user/profile.php';
    });

    $router->map('GET|POST', '/user/login/', function() {
        require 'views/user/login.php';
    });

    // Then we match requests
    $match = $route->match();
    if ($match) {
        call_user_func($match['target'], $match['params']);
    } else {
        http_response_code(404);
        die;
    }

```
By defining pages this way we actually define what the sections of our app will be and what templates we will use.

This is pretty much all you'll need to do in your index.php file. Upon call this file will be served and Mercurio will route requests to their designated paths. **Actually our whole app will happen inside index.php**, so you can add your header, footer and other page classic and universal elements in your index.php.

##### views/home.php
Our main.php file will be simple. Only a simple landing in HTML. (Your file still needs to be .php in order to be included by the PHP engine)

```html
    <h1>Hello, world!</h1>
    <p>I'm a simple Mercurio app</p>
```
If you wish you can use any templating language you want. Like [Twig](https://twig.symfony.com/) or even non PHP based like [Nunjucks](https://mozilla.github.io/nunjucks/). Mercurio will only power our app at backend level as Model and Controller and will not interfere with your frontend.

##### views/user/profile.php
Now here comes the fun and where Mercurio will really excel at. Our example app will only have basic support for simple users, but you'll still be able to see the perks of Mercurio.

```php
    $dbparams = \Mercurio\App::getDatabase();
    $database = new \Mercurio\App\Database($dbparams);

    $user = new \Mercurio\App\User;
    // $request is given by the closure provided to the router, which serves this page
    $user->getById($request['id']);
    $user = $database->get($user);

    if ($user) {
        echo $user->getHandle();
        echo $user->getNickname();
        echo "<img src=" . $user->getImg() . ">";
    } else {
        echo "User not found";
    }
```
This code first instantiates the `App\Database` model. This instance will control all Database related tasks via injections of objects into the desired methods. `Database` takes the connection parameters at instantiation. You can directly provide them or dynamically obtain them from your App settings as this code does.

To do an user selection we create a new, empty `App\User` instance and prepare it to get an user via their **id** property. Ultimately we perform the selection injecting the User model into the Database model.

>The **`getBy*()`** methods serve to prepare a selection. In order to perform such selection, object must be passed to **`Database->get()`**, this will return an instance of the same object, but loaded with the retrieved data from database, or *NULL* if the selection wasn't succesful.
>Only exceptions are **`User->getFromSession()`** and **`User->setLogin()`**, we'll talk more about these methods later.


##### views/user/login.php
You've already seen how Mercurio makes handling and retrieving users an easy task. But Mercurio does not stop there.
```php
    $factory = new \Mercurio\Utils\Form('login');
    // Mercurio form objects are built using `Nette\Forms`
    // https://github.com/nette/forms
    $form = $factory->login();

    // We can process this form like following
    if ($form->isSuccess()) {
        $values = $form->getValues();

        try {
            $dbparams = \Mercurio\App::getDatabase();
            $database = new \Mercurio\App\Database($dbparams);

            $user = new \Mercurio\App\User();
            $user->getByLogin($values['credential'], $values['password']);
            $result = $database->get($user);

            if ($user->setLogin($result)) {
                header('Location:' . APP_URL);
            }
        } catch (\Mercurio\Exception\User\LoginFailed $e) {
            echo "Login failed. Please try again";
        }
    }

    // Print form to the screen
    echo $form;
```
Again we prepare a selection, in this case `getByLogin()` will prepare an user to be selected by their handle or email, and inject it into the database to get a result. This time instead of overriding the **$user** instance, we conserve it because we need to pass the database result to `setLogin()`.

This method will terminate to perform the login validation, if succesful it will automatically load the user data into the instance and also save them into the session, later we can directly load other instances using `getFromSession()` to populate object with user data from the PHP Session.

Optionally this method can also enforce a progressive execution delay to harden bruteforce attacks, and perform blocks of 5 minutes where login is unavailable to users with too many failed attempts.

# Contributing

Mercurio is a personal project of me, born out of my desire to learn backend web technologies and build a system to optimise my sites. Still all critique, review, change and improvement over my base code will be welcome via *Issues*. Discussion about this project itself and meta talk is also very welcome.

If you have significative input to add, *Pull Requests* are open, however consider the following TODOs before submitting any changes or additions to the existing codebase:

## TODOs
1. Finish tests for existing code and fully adopt TDD.
2. Conduct tests asserting file related tasks.
3. Review and extend `Utils\Filter`.

Apart from this list, source code is full of "todo" tags, if you find one feel free to try and finish it.
