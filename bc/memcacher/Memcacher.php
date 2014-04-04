<?php
/**
 * User: inpu
 * Date: 23.12.13
 * Time: 1:00
 */

namespace bc\memcacher;

use bc\config\ConfigManager;

class Memcacher {

    const TAGS_PREFIX = 'memcache_tags_';

    /**
     * @var \Memcache
     */
    private static $mmc = null;

    /**
     * @param MemcacheObject $object
     *
     * @return bool
     */
    public static function set($object) {
        self::check();
        if(!empty($object->tags)) {
            self::assignTags($object);
        }

        return self::$mmc->set($object->key, $object->value, 0, $object->expire);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key) {
        self::check();
        $val = self::$mmc->get($key);
        if($val === false) {
            return null;
        }

        return $val;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function exists($key) {
        self::check();

        return !is_null(self::get($key));
    }

    /**
     * @param string $key
     */
    public static function del($key) {
        self::check();
        self::$mmc->delete($key);
    }

    private static function check() {
        if(is_null(self::$mmc)) {
            if(!class_exists('\Memcache')) {
                self::$mmc = new MemcacheDummy();
            } else {
                self::$mmc = new \Memcache();
            }
        }
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if(!@self::$mmc->getversion()) {
            $cfg = ConfigManager::get('config/memcache');
            if(!@self::$mmc->connect($cfg->get('host'), $cfg->get('port'))) {
                self::$mmc = new MemcacheDummy();
            }
        }
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public static function getByTag($tag) {
        $keys = self::getTaggedKeys($tag);
        $values = array();
        if(!is_null($keys)) {
            foreach($keys as $key) {
                $value = self::get($key);
                if(!is_null($value)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * @param string $tag
     */
    public static function delByTag($tag) {
        $keys = self::getTaggedKeys($tag);
        if(!is_null($keys)) {
            foreach($keys as $key) {
                self::del($key);
            }
            self::del(self::TAGS_PREFIX.$tag);
        }
    }

    /**
     * @param MemcacheObject $object
     */
    private static function assignTags($object) {
        foreach($object->tags as $tag) {
            $tags = self::get(self::TAGS_PREFIX.$tag);
            if(is_null($tags)) {
                $tags = array();
            }
            if(!in_array($object->key, $tags)) {
                $tags[] = $object->key;
            }
            self::set(new MemcacheObject(self::TAGS_PREFIX.$tag, $tags, 0));
        }
    }

    public static function flush() {
        self::check();
        self::$mmc->flush();
    }

    /**
     * @param $tag
     *
     * @return mixed
     */
    public static function getTaggedKeys($tag) {
        $keys = self::get(self::TAGS_PREFIX.$tag);

        return $keys;
    }
} 