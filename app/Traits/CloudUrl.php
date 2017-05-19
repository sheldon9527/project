<?php

namespace App\Traits;

trait CloudUrl
{
    /**
     * getCloudUrl 添加cloud的域名，可能放trait里面也挺好，先放着吧
     *
     * @param  [string] $filepath 相对路径
     * @return [url]    url
     */
    public function getCloudUrl($filepath)
    {
        if (filter_var($filepath, FILTER_VALIDATE_URL)) {
            return $filepath;
        }

        // 默认所有的图片已经在图片服务器上了
        $domain = \Config::get('filesystems.disks.qiniu.domains.https');
        return $domain . '/' . ltrim($filepath, '/');
    }
}
