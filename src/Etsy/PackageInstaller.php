<?php
namespace Etsy;

use Composer\Script\Event;

class PackageInstaller
{
    public static function postPackageInstall(Event $event)
    {
        $composer = $event->getComposer();

        $config = $composer->getConfig();

        $autoload_filepath = realpath($config->get('vendor-dir')) . '/autoload.php';

        $destination_file  = dirname(realpath(__FILE__)) . '/../../scripts/autoload.php';

        $contents = "<?php require_once('{$autoload_filepath}');";

        file_put_contents($destination_file, $contents);
    }
}