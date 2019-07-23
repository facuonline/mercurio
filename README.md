# Mercurio
Courier. Not CMS. (Still in development)

Comprehensive library to help you develop safer, better web apps in PHP.

[Contributing](#contributing)
[TODOs](#TODOs)

## Installation
    composer require mercurio/mercurio

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

**`App`** classes are the main App controller services. They encapsulate Mercurio entities and their behaviour under simple and easy to use objects and methods.

Utils and App are both part of the controller in the Model-View-Controller design pattern.

# Contributing

Mercurio is a personal project of me, born out of my desire to learn backend web technologies and build a system to optimise my sites. Still all critique, review, change and improvement over my base code will be welcome via *Issues*. Discussion about this project itself and meta talk is also very welcome.

However due to the early phase in which it is, there won't be *Pull Requests* reviewed and/or merged since the system still lacks most of the future planned features.

## TODOs
1. Finish tests for existing code and fully adopt TDD.
2. Start documentation