# dwrapd-rest-php
A RESTful implementation of dwrapd in PHP.

You can use https://github.com/WyldePointer/libdwrap-php to query this server.
<br /><br />

### Using a web server
Just put the `.php` files in your `public_html/` or `htdocs/` directory.

(nginx rewrite example: `rewrite ^(.*) http://dwrap.local$1 permanent;`)

### Standalone
```
$ git clone https://github.com/WyldePointer/dwrapd-rest-php.git
$ cd dwrapd-rest-php
$ php -S localhost:8000 index.php
```

### API examples

A record:
```
GET http://localhost:8000/get_a/www.google.com/json/limit/2
```
```
["173.194.44.82","173.194.44.81"]
```

MX record:
```
GET http://localhost:8000/get_mx/gmail.com/json/limit/2
```
```
{"alt2.gmail-smtp-in.l.google.com":20,"alt4.gmail-smtp-in.l.google.com":40}
```

### Caching
In order to enable caching, you need to have a working *Redis server* and the *PHP extension* for accessing it.
 - https://redis.io/
 - https://github.com/phpredis/phpredis/

Installing via packages in Ubuntu:
```
# apt-get install redis-server
# apt-get install php5-redis
```

Enable the caching:
`$redis_caching_enabled = true;`

Disable the caching:
`$redis_caching_enabled = false;`

Setting the caching timeout(expire):
`$redis_caching_ttl = 10;` (in seconds)

Note: By default, it uses the default redis database(`index 0`).
You can select a specific database by changing the value of `$redis_database_index` variable.

#### TODO
 - Timeout for DNS lookups and getting rid of `gethostbynam*` functions.
 - Input valiation / sanitization.
 - TXT record.
 - Logging.

