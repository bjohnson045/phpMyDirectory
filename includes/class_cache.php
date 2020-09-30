<?php
/**
* Cache Class
*  - Abstract Cache Class
*/
abstract class Cache {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var object
    */
    var $db;

    /**
    * Cache constructor
    * @param object $PMDR
    * @return Cache
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Get cached data
    * @param mixed $id
    * @param int $expireTime
    * @param string $prefix
    * @return mixed Contents on success, false on failure
    */
    function get($id, $expireTime = 0, $prefix='') {
        return $this->_get($id, $expireTime, $prefix);
    }

    /**
    * Write content to cache
    * @param mixed $id
    * @param string $content
    * @param string $prefix
    * @return void
    */
    function write($id, $content, $prefix='') {
        // Will this cause problems if the config itself is cached?
        if ($this->PMDR->getConfig('cache')) {
            $this->_write($id, $content, $prefix);
        }
    }

    /**
    * Delete cache data
    * @param mixed $id
    * @param string $prefix
    * @return boolean
    */
    function delete($id, $prefix='') {
        return $this->_delete($id, $prefix);
    }

    /**
    * Delete all files with prefix
    * @param string $prefix
    * @return void
    */
    function deletePrefix($prefix) {
        $this->_deletePrefix($prefix);
    }

    /**
    * Clear the cache
    * @return void
    */
    function clear() {
        $this->_clear();
    }

    /**
    * Abstract Cache Methods
    */
    abstract protected function _get($id, $expireTime = 0, $prefix='');
    abstract protected function _write($id, $content, $prefix='');
    abstract protected function _delete($id, $prefix='');
    abstract protected function _deletePrefix($prefix);
    abstract protected function _clear();
}

/**
* Memcache cache
*/
class Cache_Memcache extends Cache {
    /**
    * Memcache object
    * @var Memcache
    */
    protected $memcache;

    /**
    * Memcache cache constructor
    * @param object $PMDR
    * @return Cache_Memcache
    */
    function __construct($PMDR,$address,$port) {
        parent::__construct($PMDR);

        if(!class_exists('Memcache')) {
            throw new Exception('Memcache is not installed.',E_USER_NOTICE);
        }
        $this->memcache = new Memcache();
        if(!$this->memcache->connect($address,$port)) {
            throw new Exception('Unable to connect to memcache server.',E_USER_WARNING);
        }
    }

    /**
    * Get an item from the cache
    * @param string $id The ID of the cache item
    * @param int $expireTime The expiration time of the cache item
    * @param string $prefix The prefix of the cached item (example: locations_)
    * @return Memcache
    */
    protected function _get($id, $expireTime = 0, $prefix='') {
        return $this->memcache->get($prefix.md5($id));
    }

    /**
    * Write an item to the cache
    * @param string $id The ID of the cache item
    * @param mixed $content Content to cache
    * @param string $prefix The prefix of the cached item (example: locations_)
    */
    protected function _write($id, $content, $prefix='') {
        $this->memcache->set($prefix.md5($id),$content);
    }

    /**
    * Delete an item from the cache
    * @param string $id The ID of the cache item
    * @param string $prefix The prefix of the cached item (example: locations_)
    * @return Memcache
    */
    protected function _delete($id, $prefix='') {
        // Documentation says not to use the 0, but user notes says the 0 is necessary.
        return $this->memcache->delete($prefix.md5($id), 0);
    }

    /**
    * Delete an item by prefix
    * Memcache does not support this, so we have to flush the entire cache
    * @param string $prefix
    */
    protected function _deletePrefix($prefix) {
        // Can't delete just a prefix... flush everything?
        $this->memcache->flush();
    }

    /**
    * Clear the cache
    * @return void
    */
    protected function _clear() {
        $this->memcache->flush();
    }

    /**
    * Get memcache stats
    * @return mixed Stats
    */
    function getMemcacheStats() {
        return $this->memcache->getStats();
        //return $this->memcache->getExtendedStats();
    }
}

/**
* Cache Class
* Cache files to disk
*/
class Cache_File extends Cache {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var object
    */
    var $db;
    /**
    * File path to cached files
    * @var string
    */
    var $path;
    /**
    * Character to separate cache prefixes
    * @var string
    */
    var $prefix_separator = '-';

    /**
    * Cache constructor
    * @param object $PMDR
    * @return Cache
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->path = CACHE_PATH;
    }

    /**
    * Get a file from cache
    * @param mixed $id
    * @param int $expireTime
    * @param string $prefix
    * @return mixed Contents on success, false on failure
    */
    function _get($id, $expireTime = 0, $prefix='') {
        if(!$this->PMDR->getConfig('cache') OR !is_dir($this->path) OR !is_writable($this->path)) {
            return null;
        }

        $hash = md5($id);

        $file = $this->_fileName($hash,$prefix);

        if(!file_exists($file)) return null;

        if(!filesize($file)) return null;

        if(!($mtime = filemtime($file))) return null;

        if(($mtime + $expireTime) < time() AND $expireTime) {
            @unlink($file);
            return null;
        }

        if(!$fp = @fopen($file, 'rb')) {
            return null;
        }

        flock($fp, LOCK_SH);

        if(filesize($file) > 0) {
            $content = unserialize(fread($fp, filesize($file)));
        } else {
            $content = null;
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $content;
    }

    /**
    * Write content to cache
    * @param mixed $id
    * @param string $content
    * @param string $prefix
    * @return void
    */
    function _write($id, $content, $prefix='') {
        if(!$this->PMDR->getConfig('cache') OR !is_dir($this->path) OR !is_writable($this->path)) {
            return false;
        }

        $hash = md5($id);
        $file = $this->_fileName($hash,$prefix);
        $dir = dirname($file);

        if(!is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                return false;
            }
        }

        if(!$fp = fopen($file, 'cb')) {
            return false;
        }

        if(flock($fp,LOCK_EX)) {
            ftruncate($fp,0);
            fwrite($fp,serialize($content));
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            return false;
        }
        fclose($fp);
        @chmod($file, 0777);
        return true;
    }

    /**
    * Delete file from cache
    * @param mixed $id
    * @param string $prefix
    * @return boolean
    */
    function _delete($id, $prefix='') {
        if(!is_array($id)) {
            $id = array($id);
        }
        foreach($id as $cache_id) {
            $file = $this->_fileName(md5($cache_id),$prefix);
            if(!file_exists($file)) return false;
            @unlink($file);
        }
        return true;
    }

    /**
    * Delete all files with prefix
    * @param string $prefix
    * @return void
    */
    function _deletePrefix($prefix) {
        $prefix = rtrim($prefix,'_');
        if(empty($prefix) OR !file_exists($this->path.'/'.$prefix)) {
            return false;
        } else {
            unlink_directory($this->path.'/'.$prefix);
        }
    }

    /**
    * Clear the cache
    */
    function _clear() {
        unlink_files($this->path,true);
    }

    /**
    * Get a cache filename
    * @param string $hash
    * @param string $prefix
    */
    function _fileName($hash, $prefix) {
        if(!empty($prefix)) {
            $prefix = rtrim($prefix,'_').'/';
        }
        return $this->path.$prefix.substr($hash,0,2).'/'.substr($hash,2,2).'/'.$hash;
    }
}
?>