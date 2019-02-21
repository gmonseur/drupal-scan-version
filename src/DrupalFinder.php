<?php

namespace Gmo\Dsv;

use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class Version
 */
class DrupalFinder
{
    private $version;
    private $cachettl;
    private $cache;
    private $websitesCacheKey = 'finder.websites';

    /**
     * Finder constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->cachettl = $config['cachettl'];
        $this->version = new Version($config['api_url'], $config['cachettl']);
        $this->cache = new FilesystemCache();
    }

    /**
     * Get contents
     * @return array
     */
    public function getContents($finder = [])
    {
        if (!$this->cache->has($this->websitesCacheKey)) {
            if (!empty($finder)) {
                foreach ($finder as $file) {
                    $contents = $file->getContents();
                    $needle = "VERSION";
                    $pos = strpos($contents, $needle);
                    if ($pos) {
                        $version = substr($contents, $pos + strlen($needle) + 1, 10);
                        $version = getBetween($version, "'", "'");
                        $releases = $this->version->get_last_releases_from_tag($version);

                        $websites['values'][] = (object)[
                            'version' => $version,
                            'path' => $file->getPath(),
                            'releases' => $releases,
                            'status' => $this->getStatus($releases)
                        ];
                    }
                }

                $websites['cache_version'] = date("d M Y H:i:s");

                // save in cache
                $this->cache->set($this->websitesCacheKey, $websites, $this->cachettl);
            }
        }

        // retrieve the value stored by the item
        $websites = $this->cache->get('finder.websites');

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
                if ($release['release_type'] == 'Security update') {
                    $status = 'Security update';
                    break;
                } else {
                    $status = 'To update';
                }
            }
        } else {
            $status = 'Up to date';
        }
        return $status;
    }

    /**
     * Clear Cache Websites
     */
    public function clear_cache()
    {
        $this->cache->delete($this->websitesCacheKey);
    }

    /**
     * Clear All Cache
     */
    public function clear_all_cache()
    {
        $this->cache->clear();
    }
}
