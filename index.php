<?php
require_once 'config.php';

use Gmo\Dsv\DrupalFinder;
//use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;

prof_flag('Start');
$path = realpath($config['finder']['path']);
$depth = $config['finder']['depth'];

// @todo Cache ???
//$cache = new FilesystemCache();

// Drupal Finder
$drupalfinder = new DrupalFinder($config);

if (empty($drupalfinder->getContents())) {
    // Symfony Finder
    $finder = new Finder();
    $finder->in($path)->path(['core/lib', 'includes'])->name(['Drupal.php', 'bootstrap.inc'])->depth($depth);
    $websites = $drupalfinder->getContents($finder);
} else {
    // Cache version
    $websites = $drupalfinder->getContents();
}
prof_flag('End');

// Clear Cache if get request "clear_cache"
if (isset($_GET['clear_cache'])) {
    $drupalfinder->clear_all_cache();
    header('location:/');
}
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
<?php echo 'Version PHP : ' . phpversion(); ?><br>
<?php echo 'Info Server: ' . strip_tags($_SERVER['SERVER_SIGNATURE']); ?><br>
<?php echo 'Host IP : ' . getHostByName(php_uname('n')); ?>
<nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-item">
        <div class="navbar-start">
            <div class="dropdown">
                <div class="dropdown-trigger">
                    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu2">
                        <span>Content</span>
                        <span class="icon is-small">
        <i class="fas fa-angle-down" aria-hidden="true"></i>
      </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dropdown-menu2" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            <p>You can insert <strong>any type of content</strong> within the dropdown menu.</p>
                        </div>
                        <hr class="dropdown-divider">
                        <div class="dropdown-item">
                            <p>You simply need to use a <code>&lt;div&gt;</code> instead.</p>
                        </div>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item">
                            This is a link
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="navbar-end">
            <div class="navbar-item">
                <div class="tags has-addons" style="margin: 10px 15px 0 0">
                    <span class="tag">Cache Version</span>
                    <span class="tag is-primary"><?php echo $websites['cache_version']; ?></span>
                </div>

                <div class="buttons">
                    <a href="/?clear_cache" class="button">
                        Clear Cache
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <h1 class="title">Drupal Scan Version </h1>
        <p class="subtitle">
            Versions 7.x - 8.x
        </p>
        <?php if (!empty($websites)): ?>
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
                foreach ($websites['values'] as $website):
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
        <?php else: ?>
            <p>No results ...</p>
        <?php endif ?>
        <p class="content is-small">
            <?php prof_print(); ?>
        </p>
    </div>

</section>
</body>
</html>
