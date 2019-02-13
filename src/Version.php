<?php

namespace Gmo\Dsv;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class Version
 */
class Version
{
    private $versions = [];

    /**
     * Version constructor.
     * @param $versions
     */
    public function __construct($versions)
    {
        $this->versions = $versions;
    }

    /**
     * Get last releases (8.x and 7.x)
     * @return mixed
     */
    public function get_last_releases()
    {
        $last_release['8.x'] = $this->get_releases('8.x');
        $last_release['7.x'] = $this->get_releases('7.x');

        return $last_release;
    }

    /**
     * Get releases
     * @param string $version
     * @param array $filter (security, node, tag)
     * @return array
     */
    public function get_releases($version = '8.x', array $filter = [])
    {
        $security = isset($filter['security']) ? $filter['security'] : false;
        $node = isset($filter['node']) ? $filter['node'] : 1;
        $tag = isset($filter['tag']) ? $filter['tag'] : '';
        $releases = [];

        // Cache
        $cache = new FilesystemAdapter();
        // retrieve the cache item
        if ($version == '8.x') {
            $releasesResults = $cache->getItem('version.releases_results_8x');
        } elseif ($version == '7.x') {
            $releasesResults = $cache->getItem('version.releases_results_7x');
        }

        if (!$releasesResults->isHit()) {
            echo 'no cache';

            if ($version == '8.x') {
                $url = file_get_contents($this->versions['8.x']);
                // create a new item by trying to get it from the cache
                $releasesResults = $cache->getItem('version.releases_results_8x');
            } elseif ($version == '7.x') {
                $url = file_get_contents($this->versions['7.x']);
                // create a new item by trying to get it from the cache
                $releasesResults = $cache->getItem('version.releases_results_7x');
            } else {
                die('Version Error');
            }

            // assign a value to the item and save it
            $releasesResults->set($url);
            $cache->save($releasesResults);
        } else {
            echo 'cache';
        }

        // retrieve the value stored by the item
        $url = $releasesResults->get();

        $project = new \SimpleXMLElement($url);

        $i = 0;
        foreach ($project->releases[0]->release as $item) {

            if (!empty($tag) && ($item->version == $tag)) {
                break;
            }

            $flag = false;

            if ($security && isset($item->terms->term->value) && $item->terms->term->value == 'Security update') {
                $flag = true;
            } else if (!$security) {
                $flag = true;
            }

            if ($flag) {
                $releases[$i] = array(
                    'name' => $item->name,
                    'version' => $item->version,
                    'date' => date("d M Y", (int)$item->date),
                    'release_link' => $item->release_link,
                    'release_type' => $item->terms->term->value,
                );

                $i++;

                if (!empty($node) && ($i >= $node) && empty($tag)) {
                    break;
                }
            }
        }

        return $releases;
    }

    /**
     * Get last security releases (8.x and 7.x)
     * @return mixed
     */
    public function get_last_security_releases()
    {
        $filters = [
            'node' => 1,
            'security' => true
        ];
        $last_release['8.x'] = $this->get_releases('8.x', $filters);
        $last_release['7.x'] = $this->get_releases('7.x', $filters);

        return $last_release;
    }

    /**
     * Get last releases from tag
     * @param $tag
     * @return array|bool
     */
    public function get_last_releases_from_tag($tag)
    {
        if (!empty($tag)) {
            $major = $this->get_major_version($tag) . '.x';
            $last_release = $this->get_releases($major);

            if (version_compare($tag, $last_release[0]['version']) === -1) {
                $security_releases = $this->get_releases($major, ['tag' => $tag, 'security' => true]);
                $releases = array_merge($last_release, $security_releases);
                return $releases;
            }
        }
        return false;
    }

    /**
     * Get Major Version
     * @param $version
     * @return bool|string
     */
    public function get_major_version($version)
    {
        if (!empty($version)) {
            return substr($version, 0, 1);
        }
        return false;
    }
}
