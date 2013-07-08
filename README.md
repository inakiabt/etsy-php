# Scripts #

`scripts/auth-setup.php` will generate an OAuth config file required by the Etsy client to make requests.
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
  'token_secret' => 'a1234567890qwertyu',
  'token' => '3j3j3h33h3g5',
  'access_token' => '8asd8as8gag5sdg4fhg4fjfgj',
  'access_token_secret' => 'f8dgdf6gd5f4s',
);
```