# API #

Based on [Etsy Rest API description](http://www.etsy.com/developers/documentation/reference/apimethod) output, this wrapper provides a simple client with all available methods on Etsy API (thanks to the `__call` magic PHP method!), validating its arguments on each request (Take a look to https://github.com/inakiabt/etsy-php/blob/master/src/Etsy/methods.json for full list of methods and its arguments).

## Requirements

Note: I will be working on remove this dependencies
* cURL devel:
  * Ubuntu: `sudo apt-get install libcurl4-dev`
  * Fedora/CentOS: `sudo yum install curl-devel`
* OAuth pecl package:
  * `sudo pecl install oauth`
  * And then add the line `extension=oauth.so` to your `php.ini`

## Installation

The following recommended installation requires [composer](http://getcomposer.org/). If you are unfamiliar with composer see the [composer installation instructions](http://getcomposer.org/doc/01-basic-usage.md#installation).

Add the following to your `composer.json` file:

```json
{  
  "require": {
    "inakiabt/etsy-php": "dev-master"
  }
}
```

## Usage ##

All methods has only one argument, an array with two items (both are optional, depends on the method):

- *params*: an array with all required params to build the endpoint url.
  > Example:
  > [getSubSubCategory](http://www.etsy.com/developers/documentation/reference/category#method_getsubsubcategory): GET /categories/:tag/:subtag/:subsubtag
```php
  # it will request /categories/tag1/subtag1/subsubtag1
  $api->getSubSubCategory(array(
          'params' => array(
                         'tag' => 'tag1',
                         'subtag' => 'subtag1',
                         'subsubtag' => 'subsubtag1'
           )));
```

- *data*: an array with post data required by the method
  > Example:
  > [createShippingTemplate](http://www.etsy.com/developers/documentation/reference/shippingtemplate#method_createshippingtemplate): POST /shipping/templates
```php
  # it will request /shipping/templates sending the "data" array as the post data
  $api->createShippingTemplate(array(
    						'data' => array(
   							    "title" => "First API Template",
   							    "origin_country_id" => 209,
   							    "destination_country_id" => 209,
   							    "primary_cost" => 10.0,
   							    "secondary_cost" => 10.0
           )));
```

## OAuth configuration script ##
Etsy API uses OAuth 1.0 authentication, so lets setup our credentials.

The script `scripts/auth-setup.php` will generate an OAuth config file required by the Etsy client to make signed requests.
Example:
```bash
export ETSY_CONSUMER_KEY=qwertyuiop123456dfghj
export ETSY_CONSUMER_SECRET=qwertyuiop12

php scripts/auth-setup.php /path/to/my-oauth-config-destination.php
```
It will show an URL you must open, sign in on Etsy and allow the application.  Then copy paste the verification code on the terminal.
(On Mac OSX, it will open your default browser automatically)

### Generated OAuth config file ###
After all, it should looks like this:
```php
<?php
 return array (
  'consumer_key' => 'df7df6s5fdsf9sdh8gf9jhg98',
  'consumer_secret' => 'sdgd6sd4d',
  'token_secret' => 'a1234567890qwertyu',
  'token' => '3j3j3h33h3g5',
  'access_token' => '8asd8as8gag5sdg4fhg4fjfgj',
  'access_token_secret' => 'f8dgdf6gd5f4s',
);
```

## Initialization ##

```php
<?php
require('vendor/autoload.php');
$auth = require('/path/to/my-oauth-config-destination.php');

$client = new Etsy\EtsyClient($auth['consumer_key'], $auth['consumer_secret']);
$client->authorize($auth['access_token'], $auth['access_token_secret']);

$api = new Etsy\EtsyApi($client);

print_r($api->getUser(array('params' => array('user_id' => '__SELF__'))));
```

## Examples ##

```php
print_r($api->createShippingTemplate(array(
 						'data' => array(
							    "title" => "First API Template",
							    "origin_country_id" => 209,
							    "destination_country_id" => 209,
							    "primary_cost" => 10.0,
							    "secondary_cost" => 10.0
							))));

# Upload local files: the item value must be an array with the first value as a string starting with "@":
$listing_image = array(
		'params' => array(
			'listing_id' => '152326352'
		),
		'data' => array(
			'image' => array('@/path/to/file.jpg;type=image/jpeg')
));
print_r($api->uploadListingImage($listing_image));

```

## Asociations ##
You would be able to fetch associations of given your resources using a simple interface:
```php
    $args = array(
            'params' => array(
                'listing_id' => 654321
            ),
            // A list of associations
            'associations' => array(
                // Could be a simple association, sending something like: ?includes=Images
                'Images',
                // Or a composed one with (all are optional as Etsy API says) "scope", "limit", "offset", "select" and sub-associations ("associations")
                // ?includes=ShippingInfo(currency_code, primary_cost):active:1:0/DestinationCountry(name,slug)
                'ShippingInfo' => array( 
                    'scope' => 'active',
                    'limit' => 1,
                    'offset' => 0,
                    'select' => array('currency_code', 'primary_cost'),
                    // The only issue here is that sub-associations couldn't be more than one, I guess.
                    'associations' => array(
                        'DestinationCountry' => array(
                            'select' => array('name', 'slug')
                        )
                    )
                )
            )
        );
   $result = $this->api->getListing($args);
```
To read more about associations: https://www.etsy.com/developers/documentation/getting_started/resources#section_associations

## Testing ##
```bash
$ vendor/bin/phpunit src/test/
```

## Changelog

* 1.0
  * Init commit, working module.

## Author

**Iñaki Abete**  
web: http://github.com/inakiabt  
email: inakiabt+github@gmail.com  
twitter: @inakiabt  


## Contribute

Found a bug? Want to contribute and add a new feature?

Please fork this project and send me a pull request!

## License

mobiledevice is licensed under the MIT license:

www.opensource.org/licenses/MIT
