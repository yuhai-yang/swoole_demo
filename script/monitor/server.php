<?php

/**
 * 系统监控
 * Class Server
 */
class Server
{
    const PORT = 9501;

    public function port()
    {
        $shell = 'netstat -an|grep ' . self::PORT;
        $flag = shell_exec($shell);
        echo $flag;
    }
}

(new Server())->port();
