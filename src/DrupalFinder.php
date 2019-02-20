<?php

namespace Gmo\Dsv;

use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class Version
 */
class DrupalFinder
{
    private $finder = [];
    private $version;

    /**
     * Finder constructor.
     * @param $finder
     */
    public function __construct($finder, $config)
    {
        $this->finder = $finder;
        $this->version = new Version($config['api_url']);
    }

    /**
     * Get contents
     * @return array
     */
    public function getContents()
    {
        // Cache
        $cache = new FilesystemCache();

        if (!$cache->has('finder.websites')) {
            echo 'no cache websites <br>';

            foreach ($this->finder as $file) {
                $contents = $file->getContents();
                $needle = "VERSION";
                $pos = strpos($contents, $needle);
                if ($pos) {
                    $version = substr($contents, $pos + strlen($needle) + 1, 10);
                    $version = getBetween($version, "'", "'");
                    $releases = $this->version->get_last_releases_from_tag($version);

                    $websites[] = (object)[
                        'version' => $version,
                        'path' => $file->getPath(),
                        'releases' => $releases,
                        'status' => $this->getStatus($releases)
                    ];
                }
            }

            // save in cache
            $cache->set('finder.websites', $websites);

        }else{
            echo 'cache websites<br>';
        }


        // retrieve the value stored by the item
        $websites = $cache->get('finder.websites');

        return $websites;
    }

    /**
     * Get status
     * @param $releases
     * @return string
     */
    private function getStatus($releases)
    {
        if (!empty($releases)) {
            foreach ($releases as $release) {
                if (empty($release)) {
                    $status = 'Up to date';
                } else {
                    if ($release['release_type'] == 'Security update') {
                        $status = 'Security update';
                        break;
                    } else {
                        $status = 'To update';
                    }
                }
            }
        } else {
            $status = 'Other';
        }
        return $status;
    }
}
