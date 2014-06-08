<?php

/*
 * Class Name: Cacheable
 * Class URI: http://www.sacredpixel.com
 * Description: Caches content output and write it to a file
 * Version: 1.1
 * Author: Mohsen Heydari
 * Author URI: http://www.sacredpixel.com
 */
 
class FileCache
{
    protected $settings = array();

    public function  __construct(array $params=array())
    {
        $defaults = array(
            'directory' => dirname(__FILE__) . '/cache',
            'cache_time' => 300
        );

        $this->settings = array_merge($defaults, $params);

        $this->InitDir();
    }

	protected function InitDir()
	{
		if (!is_dir($this->settings['directory']))
		{
			mkdir($this->settings['directory'], 0775, true);
		}
	}
	
	//Clears the cache directory
	public function Clear()
	{
		if ($handle = opendir($this->settings['directory'])) {

			while (false !== ($file = readdir($handle))) {
				if ($file != '.' and $file != '..') {
					unlink($this->settings['directory'] . $file);
				}
			}
			
			closedir($handle);
		}
	}

    protected function GetFilePathFromKey($key)
    {
        return $this->settings['directory'] . '/' . md5($key) . '.cache';
    }

    public function GetCache($key)
    {
        $filePath = $this->GetFilePathFromKey($key);

        clearstatcache();

        // Show file from cache if still valid
        if (file_exists($filePath) &&
            (time() - $this->settings['cache_time']) < filemtime($filePath)) {

            return file_get_contents($filePath);
        }

        return false;
    }

    public function Save($key, $value)
    {
        $filePath = $this->GetFilePathFromKey($key);

        $fp = fopen($filePath, 'w');
        fwrite($fp, $value);
        fclose($fp);
    }


}
