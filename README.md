# BxAuth
Database Authentication for Billmax

This package is not written with any public use in mind. It's an experiment for allowing external apps to login using the authentication in the Billmax billing software.

## Instantiation

To connect to the database to verify authentication we need 4 pieces of information:

- host - Name/IP of billmax server. Defaults to localhost.
- name - Name of the database table. Defaults to billmax.
- user - Username of database user.
- pass - Password of database user.

### Environment variables

The constructor arguments can be provided directly, or environment variables can be used instead:

- BILLMAX_DB_HOST
- BILLMAX_DB_NAME
- BILLMAX_DB_USER
- BILLMAX_DB_PASS

See ,env.example file.

### Example - Constructor arguments

```php
$auth = new \Ocolin\BxAuth\BxAuth(
    host: 'localhost',
    name: 'billmax',
    user: 'myusername',
    pass: 'mypassword'
);
```

### Example - Environment variables

```php
$_ENV['BILLMAX_DB_HOST'] = 'localhost';
$_ENV['BILLMAX_DB_NAME'] = 'billmax';
$_ENV['BILLMAX_DB_USER'] = 'myusername';
$_ENV['BILLMAX_DB_PASS'] = 'mypassword';

$auth = new \Ocolin\BxAuth\BxAuth();
```

## Calls

### Login

- user - Username to log in as.
- pass - Password to long in with.
- session - Create a PHP session and add data to it.

```php
$data = $auth->login(
    user: 'bobsuncle',
    pass: 'tiddlywinks',
    session: true
);

print_r( $_SESSION );

Array
(
    [LOGGED_IN] => 1
    [AUTH] => Ocolin\BxAuth\AuthData Object
        (
            [id] => 86
            [access] => 31
            [descr] => Bob Uncle
            [email] => bob@staff.test.com
            [user] => bobsuncle
            [fname] => Bob
            [lname] => Uncle
            [password] => $1$UV$hR/8uhVeBpD5Wzb0V7Dyz/
        )

)
```

By default the function returns an object with the user data in it. Setting seesion to true will add that data to $_SESSION['AUTH'].

### Logout

This will log the user out, destroy the cookie, and wipe any session variables.

```php
$auth->logout();
```

### Check Login

Check to see if we are currently logged in. Return true or false.

```php

$loggedin = $auth->check_Login();

var_dump( $loggedin );

bool(true)
```
