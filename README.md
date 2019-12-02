# LogImporter
Monolog logs import tool

Required database config file named `_connection_settings.php` like this:

```
<?php

return [
    'database'  =>  'logs',
    'username'  =>  'username',
    'password'  =>  'password'
];

```