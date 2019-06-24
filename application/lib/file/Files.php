<?php
/*
* Created by DevilKing
* Date: 2019-06-08
*Time: 16:07
*/

namespace app\lib\file;

use think\facade\Config;
use app\lib\exception\file\FileException;

abstract class Files
{
    //被允许的文件类型列表
    protected $includes;

    //不被允许的文件类型列表
    protected $excludes;

    //单个文件的最大字节数
    protected $singleLimit;

    //多个文件的最大数量
    protected $totalLimit;

    //文件上传的最大数量
    protected $nums;

    //文件存储目录
    protected $storeDir;

    //文件存储对象
    protected $files;

    /**
     * Files constructor.
     * @param $files
     * @param array $config
     * @throws FileException
     */
    public function __construct($files, $config = [])
    {
        //是否传入数据
        $this->files = $files;
        $this->includes = [];
        $this->excludes = [];
        $this->singleLimit = 0;
        $this->totalLimit = 0;
        $this->nums = 0;
        $this->storeDir = '';
        //加载配置
        $this->loadConfig($config);
        //是否有文件上传
        $this->verify();
    }

    abstract public function upload();

    /**
     * @param array $config
     */
    protected function loadConfig(array $config)
    {
        $defaultConfig = Config::pull('file');
        $this->includes = $config['include'] ?? $defaultConfig['include'];
        $this->excludes = $config['exclude'] ?? $defaultConfig['exclude'];
        $this->singleLimit = $config['single_limit'] ?? $defaultConfig['single_limit'];
        $this->totalLimit = $config['total_limit'] ?? $defaultConfig['total_limit'];
        $this->nums = $config['nums'] ?? $defaultConfig['nums'];
        $this->storeDir = $config['store_dir'] ?? $defaultConfig['store_dir'];
    }

    /**
     * @throws FileException
     */
    protected function verify()
    {
        if (!$this->files) {
            throw new FileException([
                'msg' => '未找到符合条件的文件资源',
            ]);
        }
        $this->allowedFile();
        $this->allowedFileSize();
    }

    /**
     * @param $file
     * @return string
     */
    protected function generateMd5($file)
    {
        $md5 = md5_file($file->getInfo()['tmp_name']);
        return $md5;
    }

    /**
     * @param $file
     * @return mixed
     */
    protected function getSize($file)
    {
        $size = $file->getInfo()['size'];
        return $size;
    }

    /**
     * @return bool
     * @throws FileException
     */
    protected function allowedFile()
    {
        if ((!empty($this->includes) && !empty($this->exclude)) || !empty($this->includes)) {
            foreach ($this->files as $v) {
                $fileName = $v->getInfo()['name'];
                if (!strpos($fileName, '.') || !in_array(substr($fileName, strripos($fileName, ".") + 1), $this->includes)) {
                    throw new FileException([
                        'msg' => '文件扩展名不合法',
                    ]);
                }
            }
        } else if (!empty($this->excludes) && empty($this->includes)) {
            foreach ($this->files as $v) {
                $fileName = $v->getInfo()['name'];
                if (!strpos($fileName, '.') || in_array(substr($fileName, strripos($fileName, ".") + 1), $this->excludes)) {
                    throw new FileException([
                        'msg' => '文件扩展名不合法',
                    ]);
                }
            }
        }
        return true;
    }

    /**
     * @throws FileException
     */
    protected function allowedFileSize()
    {
        $fileCount = count($this->files);

        if ($fileCount > $this->nums) {
            throw new FileException([
                'msg' => '文件数量过多',
            ]);
        }
        $totalSize = 0;
        foreach ($this->files as $k => $file) {
            $fileSize = $this->getSize($this->files[$k]);
            if ($fileSize > $this->singleLimit) {
                throw new FileException([
                    'msg' => '文件体积过大',
                ]);
            }
            $totalSize += $fileSize;
        }
        if ($totalSize > $this->totalLimit) {
            throw new FileException([
                'msg' => '文件体积过大',
            ]);
        }
    }
}
