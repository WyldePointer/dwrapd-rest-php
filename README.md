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

#### TODO
 - Timeout for `gethostbynam*` functions.
 - Input valiation / sanitization.
 - MX & TXT records.

