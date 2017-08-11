# dwrapd-rest-php
A RESTful implementation of dwrapd in PHP.

Note: This project is under active development and ANYTHING may change at ANYTIME.

You can use https://github.com/WyldePointer/libdwrap-php to query this server.
<br />
Or via command-line:
```
echo -e "GET /get_mx/gmail.com/json/limit/2 HTTP/1.1\r\nHost: localhost:8000\r\n\r\n" | nc localhost 8000 | tail -n 1; echo
```
<br />


### Deploying using a web server
Just put the `.php` files in your `public_html/` or `htdocs/` directory.

### Deploying in standalone mode
```
$ git clone https://github.com/WyldePointer/dwrapd-rest-php.git
$ cd dwrapd-rest-php
$ php -S localhost:8000 index.php
```

### API examples

A record:
```
GET /get_a/www.google.com/json/limit/2
```
```
["173.194.44.82","173.194.44.81"]
```

MX record:
```
GET /get_mx/gmail.com/json/limit/2
```
```
{"alt2.gmail-smtp-in.l.google.com":20,"alt4.gmail-smtp-in.l.google.com":40}
```

### Caching
In order to enable the caching, you need to have a working *redis server* and the *PHP extension* for accessing that.
 - https://redis.io/
 - https://github.com/phpredis/phpredis/

You can also install them via packages but that highly depends on your operating system.

Ubuntu example:
```
# apt-get install redis-server
# apt-get install php5-redis
```

*The following configuration parameters are defined in your `index.php` file.*

Enable the caching:

`$redis_caching_enabled = true;`

Disable the caching:

`$redis_caching_enabled = false;`

Setting the TTL for the cached records:

`$redis_caching_ttl = 10;` (in seconds)

Note: By default, it uses the default redis database(`index 0`).

You can select a specific database by changing the value of `$redis_database_index` variable.


### Authoritative resolver
You can enable it by `$redis_authoritative_enabled = true;` in your `index.php` file.

For adding a new record to the local database, you can use the `dwrapd_add_new_record.php` CLI utility, which takes a JSON file as its first argument:
```
$ dwrapd_add_new_record.php record_file.json
```

The JSON file must be structured like:
```
{"www.server.local":{"A":["192.168.1.250","192.168.2.250","192.168.3.250"],"MX":{"mail1.local":10,"mail1.server.local":20,"server.local":30}}}
```

Which is:
```
array(1) {
  ["www.server.local"]=>
  array(2) {
    ["A"]=>
    array(3) {
      [0]=>
      string(13) "192.168.1.250"
      [1]=>
      string(13) "192.168.2.250"
      [2]=>
      string(13) "192.168.3.250"
    }
    ["MX"]=>
    array(3) {
      ["mail1.local"]=>
      int(10)
      ["mail1.server.local"]=>
      int(20)
      ["server.local"]=>
      int(30)
    }
  }
}
```

And can be made using:
```
<?php
$my_record = array(
  "www.server.local" => array (
    'A' => array ("192.168.1.250", "192.168.2.250", "192.168.3.250"),
    "MX" => array ("mail1.local" => 10, "mail1.server.local" => 20, "server.local" => 30)
  )
);

file_put_contents("record_file.json", json_encode($my_record));
die();
?>
```

Note: By default the authoritative records are stored in database `index 1` and if you want, you can change it by `$redis_authoritative_database_index` variable in your `index.php` file.


#### TODO
 - Writing a dedicated PHP extension for the name resolution. (BSD-Licensed)
 - Support for querying TXT record.
 - Logging.
 - PHP7 compatibility. (Must work by default since we're not using any PHP7-specific features.)
 - ACL. (IP / Network)
 - Authentication. (PKI and/or SSO)
 - Using an asynchronous resolver library. (https://github.com/OpenSMTPD/libasr is preferred)


### Notes on blocking
The big blocking part of this code is PHP's internal name resolution functions, which are 101% blocking, and to make the situation worse, PHP's `gethostbyname()` is just a wrapper around libc's `gethostbyname()` and/or `getaddrinfo()` which are.. crap!

So for now, you can run your own local `unbound(8)` server. (Which is highly recommended anyways, since it eliminates the need for costly remote DNS lookups.)

