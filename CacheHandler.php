<?php
namespace www\week2\day1\dinhtuan;


class CacheHandler implements CacheInterface
{
	private $cacheLvl1;
	private $cacheLvl2;

	/**
	 * constructor
	 * @return void
	 */
	public function __construct()
	{
		$this->cacheLvl1 = new MemoryCache();
	}

	/**
     * use to check if something exist in the cache
     * @param string $key key to check
     * @return true/false
     */
    public function check_exist($key)
    {
    	return $this->cacheLvl1->check_exist($key);
    }

    /**
     * use to add something to the cache
     * @return void
     */
    public function add($key, $value)
    {
    	$this->cacheLvl1->add($key, $value);
    }

    /**
     * use to get data bind by the key from the cache
     * @return value of the key
     */
    public function get($key)
    {
    	return $this->cacheLvl1->get($key);
    }
}