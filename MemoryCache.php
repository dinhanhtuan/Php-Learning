<?php
namespace www\week2\day1\dinhtuan;

class MemoryCache implements CacheInterface
{
    const MAXCACHESIZE = 200000;

    private $cacheArray = array();
    private $cacheSize;


    /**
     * constructor use for set cache size
     */
    public function __construct()
    {
        $this->cacheSize = 0;
    }

    /**
     * this function is used to check weather the key exist in cache array
     * @param string $key key to check
     * @return true/false
     */
    public function check_exist($key)
    {
        return isset($this->cacheArray[$key]);
    }

    /**
     * this function is used to push the key-value pair into the cache array
     * @param string $key key of the value
     * @param mixed $value value of the key
     * @return void
     */
    public function add($key, $value)
    {
        $this->cacheArray[$key] = $value;
    }

    /**
     * this function is used to get the value of the key from the cache array
     * @param string $key the key to get the value
     * @return mixed value of the key
     */
    public function get($key)
    {
        return $this->cacheArray[$key];
    }
}