*Scripts
`scripts/auth-setup.php` will generate an oauth config file required by the Etsy client to make requests
	Example `scripts/auth-setup.php /path/to/my-oauth-config-destination.php /path/to/etsy-app-config.php`

***etsy-app-config.php
```php
<?php
return array(
	'consumer_key' => 'asdfghjklqwertyuk',
	'consumer_secret' => 'fghjk543hj3b'
);
```
***Generated OAuth file
After all, it should looks like this:
```
<?php
 return array (
  'token_secret' => 'a1234567890qwertyu',
  'token' => '3j3j3h33h3g5',
  'access_token' => '8asd8as8gag5sdg4fhg4fjfgj',
  'access_token_secret' => 'f8dgdf6gd5f4s',
);
```
