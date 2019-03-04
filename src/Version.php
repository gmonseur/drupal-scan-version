<?php

namespace Gmo\Dsv;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class Version
 */
class Version
{
    private $versions = [];
    private $cachettl;
    private $cache;
    private $releasesCacheKey = array(
        '8.x' => 'version.releases_results_8x',
        '7.x' => 'version.releases_results_7x'
    );

    /**
     * Version constructor.
     * @param $versions
     */
    public function __construct($versions, $cachettl)
    {
        $this->versions = $versions;
        $this->cachettl = $cachettl;
        $this->cache = new FilesystemCache();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);


        $test = $this->get_last_releases();
        $jsonTest = $serializer->serialize($test, 'json');

        $fileSystem = new Filesystem();

        if($fileSystem->exists(['last_releases.txt'])){
            $current_release = file_get_contents("last_releases.txt");
            if($current_release === $jsonTest){
                echo 'ok';
            }else{
                echo 'ko';
            }
        }else{
            $fileSystem->dumpFile('last_releases.txt', $jsonTest);
        }
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

        // retrieve the cache item
        if ($version == '8.x') {
            $releasesResultsKey = $this->releasesCacheKey['8.x'];
        } elseif ($version == '7.x') {
            $releasesResultsKey = $this->releasesCacheKey['7.x'];
        }

        if (!$this->cache->has($releasesResultsKey)) {
            if ($version == '8.x') {
                $url = file_get_contents($this->versions['8.x']);
            } elseif ($version == '7.x') {
                $url = file_get_contents($this->versions['7.x']);
            } else {
                die('Version Error');
            }
            // save in cache
            $this->cache->set($releasesResultsKey, $url, $this->cachettl);
        }

        // retrieve the value stored by the item
        $url = $this->cache->get($releasesResultsKey);

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
                    'name' => (string)$item->name,
                    'version' => (string)$item->version,
                    'date' => date("d M Y", (int)$item->date),
                    'release_link' => (string)$item->release_link,
                    'release_type' => (string)$item->terms->term->value,
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

    /**
     * Clear Cache Version
     */
    public function clear_cache()
    {
        foreach ($this->releasesCacheKey as $value) {
            $this->cache->delete($value);
        }
    }
}
