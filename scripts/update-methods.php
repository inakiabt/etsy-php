<?php
$destination_file = @$argv[1];
if (empty($destination_file)) {
    error_log("Destination OAuth file is required: php download-methods.php /path/to/my-etsy-oauth-config.php");
    exit(1);
}

$consumer_key = getenv('ETSY_CONSUMER_KEY');
$consumer_secret = getenv('ETSY_CONSUMER_SECRET');

if (empty($consumer_key) || empty($consumer_secret)) {
    error_log("Env vars ETSY_CONSUMER_KEY and ETSY_CONSUMER_SECRET are required\n\nExample:\nexport ETSY_CONSUMER_KEY=qwertyuiop123456dfghj\nexport ETSY_CONSUMER_SECRET=qwertyuiop12");
    exit(1);
}

require('vendor/autoload.php');

$client = new Etsy\Client($consumer_key, $consumer_secret, $destination_file);

$api = new Etsy\Api($client);

$result = $api->getMethodTable(array());
echo indent(json_encode(buildMethods($result['results'])));

function buildMethods($results) {
    $methods = array();
    foreach ($results as $method) {
        $methods[$method['name']] = $method;
    }

    return $methods;
}

/**
 * http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}