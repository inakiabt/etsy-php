<?php

$destination_file = $argv[1];
if (empty($destination_file))
{
    echo "Destination file is required: php auth-setup.php /path/to/my-etsy-auth-config.php";
    exit(1);
}

require_once('../vendor/autoload.php');

use Etsy\EtsyClient;
use Etsy\OAuthHelper;

$client = new EtsyClient('AAAAAAAAAAAAAAAAA', 'AAAAAAAAAAAAAAAAA');
$helper = new OAuthHelper($client);

try {
    $url = $helper->requestPermissionUrl();

    /// read user input for verifier
    print "please sign in to this url and paste the verifier below: $url \n";

    // on Mac OSX
    exec("open '" . $url . "'");

    print '$ ';
    $verifier = trim(fgets(STDIN));

    $helper->getAccessToken($verifier);

    file_put_contents($destination_file, "<?php\n return " . var_export($helper->getAuth(), true) . ";");

    echo "Success! auth file '{$destination_file}' created.";
} catch (Exception $e) {
    echo $e;    
}
