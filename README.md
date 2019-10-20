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

`config.php`
```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/my-mercurio-app/'
    ]);
```

Alternatively if you have a database:
```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/my-mercurio-app/'
    ], [
        // Check Medoo for a complete list of supported database types
        // http://github.com/catfan/medoo
        'TYPE' => 'mysql', 
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);
```

This will prepare your environment to work with Mercurio. To work with the `App` classes it's necessary that you have a SQL database, either way you'll only be able to use a basic set of Mercurio tools (i.e just `Utils`).
>You can obtain a random, safe key using *`Mercurio\Utils\Random::hash()`* (hardcoding the value in the array, the app key needs to remain consistent trough the App's life)

### Utils and App
Mercurio is divided in two sets of classes. 

**`Utils`** is a list of microservices both for the system and for the developer. These classes are mostly static so their methods can be accessed on the go and don't require a database to work. Their importance varies, sometimes they'll be at the very core of our App and sometimes they won't be called once (at least directly by you).

**`App`** classes are the main services model. They encapsulate Mercurio components and their behaviour under simple and easy to use objects and methods.

>**`Utils`** are part of the **Controller** and **`App`** are the **Model** in the Model-View-Controller design pattern. All there is left to you after glueing these bricks as you wish, is to develop a nice **View** template.

There are also many custom **`Exceptions`**, but they serve mainly to provide verbose errors to developers, like `Usage`, or to add better, extended, exception behaviour out of the box, like `User\*`. As Exceptions they don't provide any functionability *per se*.

### **Your first app**
Following is only an example App. Mercurio can be used in many different ways and for many different purposes.

This example app makes use of the MVC pattern in which Mercurio is inspired, this pattern allows us to separate our app logic from the frontend logic and build apps that are easily maintenanble, scalable and even perform better. However you do not need to follow this pattern to build apps with Mercurio.


#### Folder structure
After installing mercurio your project folder sure looks something like this:

    | my-mercurio-app
        | vendor
        composer.json
        composer.lock

Our example app will now include a config.php file where we'll start our Mercurio app as seen above, an index.php file to be served and an /app/ folder where we will be making our app actually happen. Your app folder structure must look like following:

    | my-mercurio-app
        | app
            | Controllers
            | Views
        | vendor
        config.php
        composer.json
        composer.lock
        index.php

#### Routing
We will be using [`AltoRouter`](https://github.com/dannyvankooten/AltoRouter) to sort user requests, match them to a controller and in the end, just organize our app out. This package comes bundled with Mercurio.

##### URL masking
**It is necessary** that you follow this step in order for AltoRouter to work. If you use Nginx you'll need to update your **ngingx.conf** file like so:
```
    try_files $uri /index.php;
```

If you use Apache you can use `Utils\Htaccess`:
```php
    \Mercurio\Utils\Htaccess::setMasking();
```
This method will set up, if possible, URL masking via apache's mod_rewrite. Just write it in your **index.php** and run the page in your browser. Nothing will happen in your screen but Mercurio will silently set up the URL masking in your app folder.

After running it you can delete that line to avoid calling the same method over and over. It will not perform a new masking, but it will consume some resources on every request just by having to check if it needs to do the masking.

##### Request matching and controllers
AltoRouter will easily help us at listening for specific requests and respond to them:
```php
    $router = new AltoRouter;
    // This constant is provided by Mercurio
    $router->setBasePath(APP_PATH); // APP_PATH = /my-mercurio-app/

    // Usually we would add a forward slash as second parameter here
    // But since the basepath slash is already provided by the APP_PATH constant we can skip using it
    $router->map('GET', '', 'Controllers\Index');

    $router->map('GET', 'user/[i:id]/', 'Controllers\UserProfile');

    $router->map('GET|POST', 'user/login/', 'Controllers\UserLogin');

    // Then we match requests
    $match = $route->match();
    if ($match) {
        // Call controller on match
        function callController($controller) {
            $ControllerClass = str_replace('\\', DIRECTORY_SEPARATOR, $controller);
                
            require 'app' . DIRECTORY_SEPARATOR . $ControllerClass . '.php';
        }
        spl_autoload_register('callController');

        $match['target']::send($match['params']);
    } else {
        // 404 on mismatch
        http_response_code(404);
        die;
    }

```
This is pretty much all you'll need to do in your index.php file. Upon call this file will be served and Mercurio will route requests to their designated paths. **Actually our whole app will happen inside index.php**.

#### Using Controllers to serve pages
*Controllers* are the middle step between a browser request or response and our app. We use controllers to glue or backend and allow the user to interact with the *Model*, i.e our app, and backwards.

`Controllers\Index.php`
```php
    namespace Controllers;

    class Index {

        // Remember? We call this method for every controller matched to a route
        public function send() {
            // Simply load the view as a file
            include 'app/Views/Index.php';
        }

    }
```
`Views\Index.php`
```html
    <h1>Hello world!</h1>
    <p>I'm a simple Mercurio App</p>
```
That's it! Give yourself a break and watch your app greet the world in your browser screen. You'll see and understand the perks of this approach in the following steps.

>If you wish you can use any templating language you want. Like [Twig](https://twig.symfony.com/) or even non PHP based like [Nunjucks](https://mozilla.github.io/nunjucks/). Mercurio will only power our app at backend level as Model and provide various Controller utilities and will not interfere with your frontend.

##### Processing data and sending it to the views
Now here comes the fun and where Mercurio will really excel at. Our example app will only have basic support for simple users, but you'll still be able to see the perks of Mercurio.

`Controllers\UserProfile.php`
```php
    namespace Controllers;

    class UserProfile {

        public $user;

        // $request is given by the router on match at index.php
        public function send($request) {
            
            // Instantiate Database model
            $dbparams = \Mercurio\App::getDatabase();
            $database = new \Mercurio\App\Database($dbparams);

            // Instantiate an empty User model
            $user = new \Mercurio\App\User;
            // Prepare the model to be loaded with an existing user from the database
            $user->getById($request['id']);
            $user = $database->get($user);

            if (!$user) {
                echo "User not found";
            } else {
                // Save user in controller and load the view
                $this->user = $user;
                include 'app/Views/UserProfile.php';
            }
        }
    }
```
This code first instantiates the `App\Database` model. This instance will control all Database related tasks via injections of model objects into the desired methods (i.e queries). `Database` takes the connection parameters at instantiation. You can directly provide them or dynamically obtain them from your App settings as this code does.

To do an user selection we create a new, empty `App\User` instance and prepare it to get an user via their **id** property. Ultimately we perform the selection, unique result using *`get()`*, injecting the User model into the Database model, the latter will return an instance of the respective class loaded with the data or *NULL* if the selection was empty. In all other queries that don't perform a selection, it will return the resulting PDO object.

We serve this in our view as following:

`Views/UserProfile.php`
```php
    echo $this->user->getHandle();
    echo $this->user->getNickname();
```
Since the view file is **included** by the controller, we can actually access data from the controller class using *`$this`*, our view is connected to the controller and the controller to the model.

##### Login users
You've already seen how Mercurio makes handling and retrieving users an easy task. But Mercurio does not stop there.

`Controllers\UserLogin.php`
```php
    namespace Controllers;

    class UserLogin {
        
        public $message, $form;

        public function send() {
            $factory = new \Mercurio\Utils\Form;
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
                        $this->message = "Login succesful";
                    }
                } catch (\Mercurio\Exception\User\LoginFailed $e) {
                    $this->message = "Login failed. Please try again";
                }
            }

            $this->form = $form;

            include 'app/Views/UserLogin.php';
        }

    }
```
Again we prepare a selection, in this case *`getByLogin()`* will prepare an user to be selected by their handle or email, and inject it into the database to get a result. This time instead of overriding the *`$user`* instance, we conserve it because we need to pass the database result to *`setLogin()`*, so we save the result in a new variable.

This method will terminate to perform the login validation, if succesful it will automatically load the user data into the instance and also save them into the session, later we can directly load other instances using `getFromSession()` to populate object with user data from the PHP Session.

Optionally this method can also enforce a progressive execution delay to harden bruteforce attacks, and perform blocks of 5 minutes where login is unavailable to users with too many failed attempts.

Serving this login page is as easy as:
`Views/UserLogin.php`
```php
    echo $this->form;

    if (!empty($message)) {
        echo $message;
    }
```

# Contributing

Mercurio is a personal project of me, born out of my desire to learn backend web technologies and build a system to optimise my sites. Still all critique, review, change and improvement over my base code will be welcome via *Issues*. Discussion about this project itself and meta talk is also very welcome.

If you have significative input to add, *Pull Requests* are open, however consider the following TODOs before submitting any changes or additions to the existing codebase:

## TODOs
1. Rework User session tie. Make sessions be stored in the database.
2. Finish tests for existing code and fully adopt TDD.
3. Conduct tests asserting file related tasks.
4. Review and extend `Utils\Filter`.

Apart from this list, source code is full of "todo" tags, if you find one feel free to try and finish it.
