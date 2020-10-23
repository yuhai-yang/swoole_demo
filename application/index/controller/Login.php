<?php


namespace app\index\controller;


use app\common\lib\redis\Predis;
use app\common\lib\Util;
use app\common\lib\Redis;

class Login
{
    public function index()
    {
//        $phone = request()->get('phone_num');
//        $code = request()->get('code');
        $phone = $_GET['phone_num'];
        $code = $_GET['code'];
        var_dump($phone);
        var_dump($code);
        if (empty($phone) || empty($code)) {
            return Util::show(config('code.error'), '手机或验证码不能为空');
        }
        $redisCode = Predis::getInstance()->get(Redis::smsKey($phone));
        echo $redisCode;
        if ($redisCode == $code) {
            //写入redis
            $data = [
                'user' => $phone,
                'srcKey' => md5(Redis::userKey($phone)),
                'time' => time(),
                'isLogin' => true,
            ];
            Predis::getInstance()->set(Redis::userKey($phone), $data, 1200);
            return Util::show(config('code.success'), 'ok', $data);
        } else {
            return Util::show(config('code.error'), 'false');
        }
    }
}