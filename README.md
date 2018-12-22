# vtiger-language-pack-translator

vTiger Language Pack Translator - automatically via Google Translate


## Installation

Install this package via [Composer](https://getcomposer.org/).

```
composer require idokd/vtiger-language-pack-translator
```

> Note: **PHP 7.1 or later** is required.

## Basic Usage

Create VtigerTranslate object

```php
use VtigerTranslate\Translate;

$tr = new Translate([$options]); // Translates into English
```
```php
$options = array(
    'google_api_key' => '<GOOGLE_MAPS_API_KEY>',
    'source' => 'en_US',
    'target' => 'he_il',
    'vtiger' => '/home/vtiger_crm'
);
$translate = new Translate($options);

$translate->full();
```


## Advanced Usage

Translate module
```php
$translate->module('Accounts');
```
Also, you can also translate a specific file
```php
$translate->module('Accounts.php');
```

TODO: overwrite flag, read from zip, package to zip
### Language Detection


### HTTP Client Configuration

This package uses [Guzzle](https://github.com/guzzle/guzzle) for HTTP requests. You can pass an array of [guzzle client configuration options](http://docs.guzzlephp.org/en/latest/request-options.html) to the options parameter to `Translate` constructor.

You can configure proxy, user-agent, default headers, connection timeout and so on using this options.

```php
$tr = new Translate([
    'timeout' => 10,
    'proxy' => [
        'http'  => 'tcp://localhost:8125',
        'https' => 'tcp://localhost:9124'
    ],
    'headers' => [
        'User-Agent' => 'Foo/5.0 Lorem Ipsum Browser'
    ]
]);
```

For more information, see [Creating a Client](http://guzzle.readthedocs.org/en/latest/quickstart.html#creating-a-client) section in Guzzle docs (6.x version).

### Errors and Exception Handling

Methods throw following Exceptions:

 - `ErrorException` If the HTTP request fails for some reason.
 - `UnexpectedValueException` If data received from Google cannot be decoded.

## Disclaimer

This package is uses Google Translate API key - you should consult google for any costs hat may apply, Consider buying [Official Google Translate API](https://cloud.google.com/translate/) for other types of usage.

## Donation

If this package helped you reduce your time to develop something, or it solved any major problems you had, feel free give me a cup of coffee :)

 - [PayPal](https://paypal.me/idokd)
