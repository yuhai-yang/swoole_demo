<?php

use think\Container;

$http = new Swoole\Http\Server('127.0.0.1', 9501);

$http->set(
    [
        'enable_static_handler' => true,
        'document_root' => '/Users/yyhaier/code/swoole/swoole_project_demo/think/public/static',
        'worker_num' => 5
    ]
);
$http->on('WorkerStart', function ($server, $worker_id) {
    // thinkphp
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';
});

$http->on('request', function ($request, $response) {
//    $_SERVER = [];
    if (isset($request->server)) {
        foreach ($request->server as $k => $v) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }
    if (isset($request->header)) {
        foreach ($request->header as $k => $v) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }
    $_GET = [];
    if (isset($request->get)) {
        foreach ($request->get as $k => $v) {
            $_GET[$k] = $v;
        }
    }
    $_POST = [];
    if (isset($request->post)) {
        foreach ($request->post as $k => $v) {
            $_POST[$k] = $v;
        }
    }
    ob_start();
    $response->setHeader("Content-type", "text/html;charset=UTF-8");
    try {
        // 执行应用并响应
        Container::get('app')->run()->send();
    } catch (\Exception $e) {
        //TODO
    }
//    echo "-action-" . request()->action() . PHP_EOL;
    $res = ob_get_contents();
    if (ob_get_contents()) ob_end_clean();
    $response->end($res);

});

$http->start();
