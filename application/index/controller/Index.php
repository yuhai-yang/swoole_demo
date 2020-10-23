<?php

namespace app\index\controller;

class Index
{
    public function index()
    {
        return '';//不能修改 否则会导致swoole开启时生成大量无用输出
    }

    public function hello()
    {
        return 1;
    }

    public function sms()
    {

    }
}
