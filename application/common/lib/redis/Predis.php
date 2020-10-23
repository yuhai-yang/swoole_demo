<?php

namespace app\common\lib\redis;
/**
 * 同步redis
 * Class Predis
 * @package app\common\lib\redis
 */
class Predis
{
    private static $_instance = null;

    public $redis = '';

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->redis = new \Redis();
        $connect = $this->redis->connect(config('redis.host'), config('redis.port'), config('redis.time_out'));
        if ($connect === 'false') {
            throw new \Exception('Redis Connect False');
        }
    }

//    public function __call($name, $arguments)
//    {
//        echo $name;//方法名
//        echo $arguments;//参数array
//        return [];
//    }

    /**
     * 写入redis
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0)
    {
        if (!$key) {
            return '';
        }
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (!$time) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }

    /**
     * 获取redis值
     * @param $key
     * @return bool|mixed|string
     */
    public function get($key)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * 添加集合
     * @param $key
     * @param $value
     * @return bool|int
     */
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($key, $value);
    }

    /**
     * 删除集合
     * @param $key
     * @param $value
     * @return int
     */
    public function sRem($key, $value)
    {
        return $this->redis->sRem($key, $value);
    }

    /**
     * 获取集合
     * @param $key
     * @return array
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }
}