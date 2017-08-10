# dwrapd-rest-php
A RESTful implementation of dwrapd in PHP.

You can use https://github.com/WyldePointer/libdwrap-php to query this server.
<br /><br />

### Using a webserver
Just put the `.php` files in your `public_html/` or `htdocs/` directory.

### Standalone
```
$ cd dwrapd-rest-php
$ php -S localhost:8000 index.php
```

### API examples
```
GET http://localhost:8000/get_ip_by_name/www.google.com
GET http://localhost:8000/get_ip_by_name/www.google.com/limit/3
GET http://localhost:8000/get_ip_by_name/www.google.com/json
GET http://localhost:8000/get_ip_by_name/www.google.com/json/limit/2
```

#### TODO
 - Input valiation / sanitization.
 - MX & TXT records.

