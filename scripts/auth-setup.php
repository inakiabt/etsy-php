<?php

$destination_file = @$argv[1];
if (empty($destination_file))
{
    error_log("Destination OAuth file is required: php auth-setup.php /path/to/my-etsy-oauth-config.php");
    exit(1);
}

$consumer_key = getenv('ETSY_CONSUMER_KEY');
$consumer_secret = getenv('ETSY_CONSUMER_SECRET');

if (empty($consumer_key) || empty($consumer_secret))
{
    error_log("Env vars ETSY_CONSUMER_KEY and ETSY_CONSUMER_SECRET are required\n\nExample:\nexport ETSY_CONSUMER_KEY=qwertyuiop123456dfghj\nexport ETSY_CONSUMER_SECRET=qwertyuiop12");
    exit(1);
}

require_once(dirname(realpath(__FILE__)) . '/../vendor/autoload.php');

use Etsy\EtsyClient;
use Etsy\OAuthHelper;

$client = new EtsyClient($consumer_key, $consumer_secret);
$helper = new OAuthHelper($client);

try {
    $url = $helper->requestPermissionUrl();

    /// read user input for verifier
    print "Please sign in to this url and paste the verifier below: $url \n";

    // on Mac OSX
    exec("open '" . $url . "'");

    print '$ ';
    $verifier = trim(fgets(STDIN));

    $helper->getAccessToken($verifier);

    file_put_contents($destination_file, "<?php\n return " . var_export($helper->getAuth(), true) . ";");

    echo "Success! auth file '{$destination_file}' created.\n";
} catch (Exception $e) {
    error_log($e);
}
