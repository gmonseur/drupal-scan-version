<?php
require_once 'config.php';

use Gmo\Dsv\Version;
use Symfony\Component\Finder\Finder;

prof_flag('Start');

$version = new Version($config['api_url']);
//$last_release = $version->get_last_releases();
//$last_security_release = $version->get_last_security_releases();
$releases_8x = $version->get_last_releases_from_tag('8.2.0');
$test = $version->get_releases('8.x', ['tag' => '8.2.0', 'security' => true]);
//trace($last_release);
//trace($last_security_release);
trace($releases_8x);
trace($test);
prof_flag('Version');
//echo version_compare('8.69', '8.67');

////////////////////////////////////////////////

$path = realpath('../');
$depth = ['> 2', '< 6'];

$finder = new Finder();
$finder->in($path)->path(['core/lib', 'includes'])->name(['Drupal.php', 'bootstrap.inc'])->depth($depth);

foreach ($finder as $file) {
    // dumps the absolute path
    $contents = $file->getContents();
    $needle = "VERSION";
    $pos = strpos($contents, $needle);
    if ($pos) {
        trace($file->getRelativePathname());
        trace($file->getPath());
        $version = substr($contents, $pos + strlen($needle) + 1, 10);
        $version = getBetween($version, "'", "'");
        echo $version;
    }
}
prof_flag('End');

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
<?php echo prof_print(); ?>
</body>
</html>
