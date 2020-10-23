<?php

namespace app\admin\controller;

use app\common\lib\Util;
use think\File;

class Image
{
    public function index()
    {

        $file = request()->file('file');
        $info = $file->move('../public/static/uploads');
        if ($info) {
            $data = [
                'image' => config('live.host') . "/uploads/" . $info->getSaveName()
            ];
            return Util::show(config('code.success'), 'ok', $data);
        } else {
            // 上传失败获取错误信息
            return Util::show(config('code.error'), $file->getError());
        }
    }
}