<?php


namespace app\common\lib;

/**
 * 异步redis
 * Class Redis
 * @package app\common\lib
 */
class Redis
{
    /**
     * 验证码前缀
     * @var string
     *
     */
    public static $pre = 'sms_';
    public static $userPre = 'user_';

    /**
     * 存储验证码redis的key值
     * @param $phone
     * @return string
     */
    public static function smsKey($phone)
    {
        return self::$pre . $phone;
    }

    /**
     * 用户key
     * @param $user
     * @return string
     */
    public static function userKey($user)
    {
        return self::$userPre . $user;
    }

}