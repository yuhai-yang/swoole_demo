<?php

namespace app\common\lib\task;


use app\common\lib\Redis;
use app\common\lib\redis\Predis;

/**
 * swoole中所有task异步分发处理
 * Class Task
 * @package app\common\lib\task
 */
class Task
{
    /**
     * 异步发送验证码
     * @param $data
     * @param $server
     * @return string
     */
    public function sendSms($data, $server)
    {
        /***发送接口***/
        sleep(3);
        $res = 'OK';
        /***发送接口***/
        if ($res == 'OK') {
            //Predis
            Predis::getInstance()->set(Redis::smsKey($data['phone']), $data['code'], config('redis.out_time'));
        } else {
            return false;
        }

        return 'OK';
    }

    /**
     * 推送直播数据
     * @param $data
     * @param $server
     */
    public function pushLive($data, $server)
    {
        $clients = Predis::getInstance()->sMembers(config('redis.live_redis_key'));
        foreach ($clients as $fd) {
            $server->push($fd, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }
}