# Mercurio
Courier. Not CMS. (Still in development)

Comprehensive library to help you develop safer, better web apps in PHP.

[Contributing](#contributing)
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
        'TYPE' => 'mysql', 
        // check catfan/medoo for a complete list of supported database types
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);
```

This will prepare your environment to work with Mercurio. To work with all of the `App` classes and most `Utils` it's necessary that you have a SQL database, either way you'll only be able to use a basic set of Mercurio tools (i.e just some Utils).

### Utils and App
Mercurio is divided in two sets of classes. 

**`Utils`** is a list of microservices both for the system and for the developer. These classes are mostly static so their methods can be accessed on the go and for the most part don't require a database to work. Their importance varies, sometimes they'll be at the very core of our App and sometimes they won't be called once.

**`App`** classes are the main App model services. They encapsulate Mercurio entities and their behaviour under simple and easy to use objects and methods.

>**Utils** are part of the **Controller** and **App** are the **Model** in the Model-View-Controller design pattern. So all is left to you after glueing these bricks as you wish, is to develop a nice **View** template.

### **Your first app**
Mercurio is not a framework, it does not force on you any scheme, practices, etc. in fact Mercurio tries hard to adapt to whatever app you try to build. That said, let's start making an example app.

#### Folder structure
After installing mercurio your project folder sure looks something like this:

    | my-mercurio-app
        | vendor
        composer.json
        composer.lock

Our example app will now include an index.php file where we'll start our Mercurio app as seen above. And a /views/ folder where we will be storing our different view templates.

#### Single page application schema
Mercurio is built with a single page application in mind. I know what I just said, you can read it again if you want. I lied. Mercurio forces you to work with a single page schema and use 3 predefined query components.

We will be using `\Mercurio\Utils\URL` to sort user query requests, serve the correct view template and in the end, just organize our app out.

##### URL masking

```php
    \Mercurio\Utils\URL::setUrlMasking();
```
This method will set up, if possible, URL masking via apache mod_rewrite. Just write it in your **index.php** and run the page in your browser. Nothing will happen in your screen but Mercurio will silently set up the URL masking in your app folder.

After running it you can delete that line to avoid calling the same method over and over. It will not perform a new masking, but it will consume some resources on every request just by having to check if it needs to do the masking.

>**It is not necessary** that you run this method. If you skip this step your app will still work, just not with nice URLs.

##### Templating
Now we will be defining what pages our app will have. There are 3 URL query components in Mercurio:
1. Pages
2. Targets
3. Actions

>Except for the targets, that serve the only purpose of identifying database entities, pages and actions don't really mean nothing yet. They are both defined by your app on runtime, it's your decision what pages to use and what actions to perform.

After having set up URL masking, these query components will appear in our URLs as following:
    
    page/target/action

Example:

    http://localhost/my-mercurio-app/user/jon/edit

Or if you didn't set up URL masking:

    http://localhost/my-mercurio-app/?page=user&target=jon&action=edit

To organize our app view we will define some pages.

```php 
    \Mercurio\Utils\URL::getPage('/', function() {
        include 'views/pages/main.php';
    });
    // '/' is an alias for the main page, i.e an empty page query.

    \Mercurio\Utils\URL::getPage('user', function() {
        include 'views/pages/users.php';
    });
```
By defining pages this way we actually define what the sections of our app will be and what templates we'll use.

And that's pretty much all you'll need to do in your index.php file. Upon call this file will be served and Mercurio will route requests to their designated paths. **Actually our whole app will happen inside index.php** much like a React.js app, just in PHP, so you can add your header, footer and other page classic and universal elements in your index.php.

##### main.php
Our main.php file will be simple. Only a simple landing in HTML. (Your file still needs to be .php in order to be included by the PHP engine)

```html
    <h1>Hello, world!</h1>
    <p>I'm a simple Mercurio app</p>
```
If you wish you can use any templating language you want. Like [Twig](https://twig.symfony.com/) or even non PHP based like [Nunjucks](https://mozilla.github.io/nunjucks/). Mercurio will only power our app at backend level as Model and Controller and will not interfere with your frontend.

##### users.php
Now here comes the fun and where Mercurio will really excel at. Our example app will only have basic support for simple users, but you'll still be able to see the perks of Mercurio.

```php
$user = new \Mercurio\App\User;

if ($user->get()) {
    include 'user_profile.php';
} else {
    include 'user_login.php';
}
```
This code first instantiates the `App\User` model. With the `get` method we can automatically load an user from the **database** into instance. Mercurio will try to find an user in the following order:
1. User at self instance.
2. User by hint at Target query.
3. User in session. (Logged in)

>Alternatively you can directly provide an user hint (user string handle, email or numeric id) to bypass this list.

If get does not find an user for us to work with it will return **false**, else it will return an array with user's info and load the instance.
>Alternatively you can provide a function as second argument to directly access user data without loading instance.

##### user_profile.php
```php
<h1><?php echo $user->getHandle(); ?></h1>
<p><?php echo $user->getNickname(); ?></p>
<img src="<?php echo $user->getImg(); ?>">
```
##### user_login.php
You've already seen how Mercurio makes handling and retrieving users an easy task. But Mercurio does not stop there.
```php
    $form = new \Mercurio\Utils\Form;
    // Mercurio form objects are an extension of Nette\Forms
    // they only add an extra layer of security against SPAM 

    $form->addText('username', 'Username or email:')
        ->setRequired(true);
    $form->addPassword('password', 'Password:')
        ->setRequired(true);
    $form->addSubmit('login', 'Enter');
    echo $form;
```

We can process this form like following:
```php
    if ($form->isSuccess()) {
        $values = $form->getValues();
        $user = new \Mercurio\App\User;

        try {
            $user->login($values['username'], $values['password'], 
            function() {
                header('Location:');
            });
        } catch (\Mercurio\Exception\User\WrongLoginCredential $e) {
            echo "Wrong username or password";
        } catch (\Mercurio\Exception\User\LoginBlocked $e) {
            echo "Login blocked. Try again in 5 minutes.";
        }
    }
```
And thats really it. We've sucessfully built an app with users that is secure, fast and easy to develop and extend.

# Contributing

Mercurio is a personal project of me, born out of my desire to learn backend web technologies and build a system to optimise my sites. Still all critique, review, change and improvement over my base code will be welcome via *Issues*. Discussion about this project itself and meta talk is also very welcome.

If you have significative input to add, *Pull Requests* are open, however consider the following TODOs before submitting any changes or additions to the existing codebase:

## TODOs
1. Implement dependency injection on App classes
2. Work `Utils\Router` as a well optimized Router and request handler.
3. Finish tests for existing code and fully adopt TDD.
4. Conduct tests asserting file related tasks.
5. Conduct tests asserting database related tasks.