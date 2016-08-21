# Beluga.Web

Some helpful web context classes :-)

```bash
composer require sagittariusx/beluga.web
```

or include it inside you're composer.json

```json
{
   "require": {
      "sagittariusx/beluga.web": "^0.1.0"
   }
}
```


The library declares the main classes inside the `Beluga\Web` namespace:

* `Beluga\Web\TopLevelDomain`:
   Represents a TLD (top level domain)
* `Beluga\Web\SecondLevelDomain`:
   Represents a second level domain (include a TLD)
* `Beluga\Web\Domain`:
   Represents a domain (include a second level domain)
* `Beluga\Web\MailAddress`:
   A mail address.
* `Beluga\Web\Url`:
   A URL (web address)
* `Beluga\Web\WebError`:

The sub namespace `Beluga\Web\Http` declares the following:

* `Beluga\Web\Http\Header`:
   A static helper class for sending different HTTP headers.
* `Beluga\Web\Http\Request`:
   A class for sending an HTTP request.
* `Beluga\Web\Http\RequestType`:
   A HTTP request type enumeration
* `Beluga\Web\Http\Response`:
   A HTTP response.
