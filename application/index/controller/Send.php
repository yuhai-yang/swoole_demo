<?php


namespace app\index\controller;


use app\common\lib\Redis;
use app\common\lib\Util;

class Send
{
    public function index()
    {
        $phoneNum = request()->get('phone_num');
        if (empty($phoneNum)) {
            return Util::show(config('code.error'), '手机号不能为空');
        }

//        $code = rand(100000,999999);
        $code = 123456;
        $taskData = [
            'method' => 'sendSms',
            'data' => [
                'phone' => $phoneNum,
                'code' => $code
            ],
        ];
        /**SEND**/
        $res = $_POST['http_server']->task($taskData);//将发送短信任务给task事务
        return Util::show(config('code.success'), '成功');
        /**SEND**/
        if ($res == 'OK') {
            //redis
            $redis = new \Swoole\Coroutine\Redis();
            $redis->connect(config('redis.host'), config('redis.port'));
            $redis->set(Redis::smsKey($phoneNum), $code, config('redis.out_time'));
            return Util::show(config('code.success'), '成功');
        } else {
            return Util::show(config('code.error'), '发送失败');
        }
    }
}