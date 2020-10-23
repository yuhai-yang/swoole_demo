<?php

return [
    'host' => '127.0.0.1',
    'port' => 6379,
    'out_time' => 120,//120秒失效
    'time_out' => 5,//连接超时时间5秒
    'live_redis_key' => 'live_game_key_'//加入直播Fd前缀
];
