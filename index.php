<?php
require_once 'config.php';

use Gmo\Dsv\Version;

$version = new Version($config['api_url']);
//$last_release = $version->get_last_releases();
//$last_security_release = $version->get_last_security_releases();
$releases_8x = $version->get_last_releases_from_tag('8.2.0');
$test = $version->get_releases('8.x', ['tag' => '8.2.0', 'security' => true]);
//trace($last_release);
//trace($last_security_release);
trace($releases_8x);
trace($test);

echo version_compare('8.69', '8.67');
////////////////////////////////////////////////

/*
$path = realpath('../');
$find = 'core/lib';
$depth = ['> 2', '< 6'];

$finder = new Finder();
$finder->in($path)->path('core/lib')->name('Drupal.php')->depth($depth);

foreach ($finder as $file) {
    // dumps the absolute path
    trace($file->getRelativePathname());
    trace($file->getPath());

    $contents = $file->getContents();
    $needle = "VERSION";
    $pos = strpos($contents, $needle);
    $version = substr($contents, $pos + strlen($needle), 10);
    $version = getBetween($version, "'", "'");
    echo $version;
}
*/
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

</body>
</html>
