<?php
namespace www\week2\day1\dinhtuan;
interface CacheInterface
{
    /**
     * use to check if something exist in the cache
     * @param string $key key to check
     */
    public function check_exist($key);

    /**
     * use to add something to the cache
     */
    public function add($key, $value);

    /**
     * use to get data bind by the key from the cache
     *
     */
    public function get($key);
}