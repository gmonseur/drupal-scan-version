<?php
require_once 'config.php';

use Gmo\Dsv\DrupalFinder;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;

prof_flag('Start');
$path = realpath($config['finder']['path']);
$depth = $config['finder']['depth'];

// Cache
$cache = new FilesystemCache();

if (!$cache->has('finder.finder_results')) {
    echo 'no cache finder <br>';
    // Symfony Finder
    $finder = new Finder();
    $finder->in($path)->path(['core/lib', 'includes'])->name(['Drupal.php', 'bootstrap.inc'])->depth($depth);
    // save in cache
    $cache->set('finder.finder_results', $finder);

} else {
    echo 'cache finder <br>';
}
// retrieve the value stored by the item
$finder = $cache->get('finder.finder_results');


// remove the cache key
//$cache->delete('stats.products_count');

// clear *all* cache keys
//$cache->clear();

$drupalfinder = new DrupalFinder($finder, $config);
$websites = $drupalfinder->getContents();
prof_flag('End');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drupal Scan Version</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.4/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
</head>
<body>
<section class="section">
    <div class="container">
        <h1 class="title">Drupal Scan Version</h1>
        <p class="subtitle">
            Versions 7.x - 8.x
        </p>

        <table class="table is-hoverable is-fullwidth">
            <thead>
            <tr>
                <th>Project</th>
                <th>Version</th>
                <th>Status</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>Project</th>
                <th>Version</th>
                <th>Status</th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            foreach ($websites as $website):
                if ($website->status == 'Up to date') {
                    $class_tag = 'is-primary';
                } elseif ($website->status == 'Security update') {
                    $class_tag = 'is-danger';
                } else {
                    $class_tag = 'is-warning';
                }
                ?>
                <tr>
                    <td>
                        <div class="columns is-multiline is-mobile">
                            <div class="column is-full">
                                <strong><?php echo $website->path; ?></strong>
                            </div>
                            <?php if (!empty($website->releases)): ?>
                                <div class="column is-full content is-small">
                                    <?php foreach ($website->releases as $release): ?>
                                        <p><?php echo $release['release_type'] . ' : <a href="' . $release['release_link'] . '" target="_blank">' . $release['version'] . '</a> (' . $release['date'] . ')'; ?></p>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="tags has-addons are-medium">
                            <span class="tag is-dark">Drupal</span>
                            <span class="tag <?php echo $class_tag ?>"><?php echo $website->version; ?></span>
                        </div>
                    </td>
                    <td><?php echo $website->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="content is-small">
            <?php prof_print(); ?>
        </p>
    </div>

</section>
</body>
</html>
