<?php
require_once 'config.php';

use Gmo\Dsv\DrupalFinder;
use Symfony\Component\Finder\Finder;

prof_flag('Start');
$path = realpath($config['finder']['path']);
$depth = $config['finder']['depth'];

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
<nav class="navbar is-spaced" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand is-marginless">
            <span
                class="is-size-3">Drupal Scan Version</span>
            <div class="tags has-addons" style="margin: 12px 15px 0 15px">
                <span class="tag">Cache Version</span>
                <span class="tag is-primary"><?php echo $websites['cache_version']; ?></span>
            </div>

            <div class="tags has-addons" style="margin: 12px 15px 0 0">
                <span class="tag">Php Version</span>
                <span class="tag is-primary"><?php echo phpversion(); ?></span>
            </div>
        </div>

        <div class="navbar-end">
            <div class="navbar-item">
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

<main class="bd-main">
    <section class="hero is-primary" style="margin-bottom: 20px">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    <?php echo 'IP ' . getHostByName(php_uname('n')); ?>
                </h1>
                <h2 class="subtitle">
                    Versions 7.x - 8.x
            </div>
        </div>
    </section>

    <div class="container">
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
    </div>
</main>

<footer class="footer">
    <div class="content has-text-centered">
        <p>
            <strong>Drupal Scan Version</strong> by <a href="https://gmonseur.be" target="_blank">Grégory Monseur</a>.
        </p>
        <p>
            <span class="icon">
                <i class="fas fa-clock"></i>
            </span>
            Page generated in <?php echo prof_print(true); ?> seconds.
        </p>
        <p>
            <a href="https://bulma.io/" target="_blank">
                <img src="https://bulma.io/images/made-with-bulma.png" alt="Made with Bulma" width="128" height="24">
            </a>
        </p>
    </div>
</footer>
</body>
</html>
