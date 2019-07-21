# Mercurio
Courier. Not CMS. (Still in development)

Comprehensive library to help you develop safer, better web apps in PHP.

[Contributing](#contributing)

## Installation
    composer require mercurio/mercurio

## Usage
Once installed you must start Mercurio with some basic configurations.

```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/mercurio'
    ]);
```

Alternatively if you have a database:
```php
    \Mercurio\App::setApp([
        'KEY' => 'your_secret_key',
        'URL' => 'http://localhost/mercurio'
    ], [
        'HOST' => 'localhost',
        'USER' => 'root',
        'PASS' => '',
        'NAME' => 'mercurio'
    ]);
```

This will prepare your environment to work with Mercurio. To work with all of the `App` classes and most `Utils` it's necessary that you have a SQL database, either you'll only be able to use a basic set of Mercurio tools.

### Utils and App
Mercurio is divided in two sets of classes. 

**`Utils`** is a list of helper and worker tools both for the system and for the developer. These classes are mostly static so their methods can be accessed on the go and for the most part don't require a database to work.

**`App`** classes are the main framework. They encapsulate Mercurio entities and their behaviour.

# Contributing

Mercurio is a personal project of me, born out of my desire to learn backend web technologies and build a system to optimise my sites. Still all critique, review, change and improvement over my base code will be welcome via *Issues*.

However due to the early phase in which it is, there won't be pull requests reviewed and/or merged since the system still lacks most of the future planned features.

## TODOs
1. Polish **Utils\URL**. Build a stable, understandable set of methods to easily manage urls.
2. Improve **App\User** behaviour around user retrieval and session read.
3. Improve **Utils\Session** segmentation.
4. Finish **App\Messages**
