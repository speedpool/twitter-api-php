<?php

class Twitter
{
    const CACHE_USER_FILENAME = 'user.json';

    const CACHE_USER_AGE = 600;

    const CACHE_USER_TIMELINE_FILENAME = 'user_timeline.json';

    const CACHE_USER_TIMELINE_AGE = 600;

    protected $connection;

    protected $cache = true;

    protected $cacheDir = 'cache/';

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->decode_json = false;
    }

    public function getUser()
    {
        if ($this->isCached(self::CACHE_USER_FILENAME, self::CACHE_USER_AGE)) {
            $json = $this->getCached(self::CACHE_USER_FILENAME);
        } else {
            $params = array(
                'include_entities' => true,
                'skip_status' => true,
            );

            $json = $this->request('account/verify_credentials', $params);
            $this->cache(self::CACHE_USER_FILENAME, $json);
        }

        return $json;
    }

    public function getUserTimeline($count = 10, $screen_name = '', $trim_user = false)
    {
        $prefix = $count . '-';
        $prefix .= ($screen_name !== '') ? preg_replace('/[^a-z0-9]/', '-', strtolower($screen_name)) . '-' : '';
        $prefix .= ($trim_user === true) ? 'trim_user-' : '';

        $cacheFilename = $prefix . self::CACHE_USER_TIMELINE_FILENAME;

        if ($this->isCached($cacheFilename, self::CACHE_USER_TIMELINE_AGE)) {

            $json = $this->getCached($cacheFilename);
        } else {
            $params = array(
                'count' => $count,
                'include_rts' => true,
            );

            if ($screen_name !== '') {
                $params['screen_name'] = $screen_name;
            }

            if ($trim_user) {
                $params['trim_user'] = true;
            }

            $json = (string) $this->request('statuses/user_timeline', $params);
            $this->cache($cacheFilename, $json);
        }

        return $json;
    }

    public function request($endpoint, $params = array())
    {
        return $this->connection->get($endpoint, $params);
    }

    public function cache($filename, $json)
    {
        file_put_contents($this->cacheDir . $filename, $json);
    }

    public function getCached($filename)
    {
        return file_get_contents($this->cacheDir . $filename);
    }

    public function isCached($filename, $age)
    {
        return $this->cache
            && file_exists($this->cacheDir . $filename)
            && filectime($this->cacheDir . $filename) > (time() - $age);
    }
}
