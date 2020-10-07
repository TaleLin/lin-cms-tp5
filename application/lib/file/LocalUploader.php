<?php
/*
* Created by DevilKing
* Date: 2019-06-08
*Time: 16:19
*/

namespace app\lib\file;

use app\api\model\admin\LinFile;
use app\lib\exception\file\FileException;
use LinCmsTp\File;
use think\facade\Config;
use think\facade\Env;

/**
 * Class LocalUploader
 * @package app\lib\file
 */
class LocalUploader extends File
{
    /**
     * @return array
     * @throws FileException
     */
    public function upload()
    {
        $ret = [];
        $host = Config::get('file.host') ?? "http://127.0.0.1:5000";
        foreach ($this->files as $key => $file) {
            $md5 = $this->generateMd5($file);
            $exists = LinFile::get(['md5' => $md5]);
            if ($exists) {
                array_push($ret, [
                    'id' => $exists['id'],
                    'key' => $key,
                    'path' => $exists['path'],
                    'url' => $host . '/' . $this->storeDir . '/' . $exists['path']
                ]);
            } else {
                $size = $this->getSize($file);
                $info = $file->move(Env::get('root_path') . '/' . 'public' . '/' . $this->storeDir);
                if ($info) {
                    $extension = '.' . $info->getExtension();
                    $path = str_replace('\\', '/', $info->getSaveName());
                    $name = $info->getFilename();
                } else {
                    throw new FileException([
                        'msg' => "存储本地文件失败",
                        'error_code' => 60001
                    ]);
                }
                $linFile = LinFile::create([
                    'name' => $name,
                    'path' => $path,
                    'size' => $size,
                    'extension' => $extension,
                    'md5' => $md5,
                    'type' => 1
                ]);
                array_push($ret, [
                    'id' => $linFile->id,
                    'key' => $key,
                    'path' => $path,
                    'url' => $host . '/' . $this->storeDir . '/' . $path
                ]);

            }

        }
        return $ret;
    }
}
