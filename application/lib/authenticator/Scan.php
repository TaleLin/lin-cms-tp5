<?php


namespace app\lib\authenticator;


use think\facade\Env;

class Scan
{
    // 控制器层命名空间
    private $controller_namespace;
    // 控制器层绝对路径
    private $controller_path;
    // 需要权限扫描的命名空间列表
    private $authScanNamespaceList;

    public function __construct()
    {
        // 指定控制器层的命名空间
        $this->controller_namespace = 'app\\api\\controller\\';
        // 拼接出当前应用模块下的控制器层目录在服务器上的绝对路径
        $this->controller_path = Env::get('module_path') . 'controller';
        // 初始化需权限扫描的命名空间列表
        $this->authScanNamespaceList = [];
    }

    /**
     * 入口方法，调用scanControllerLayerDir（）扫描控制器层
     * @return array  控制器层下所有类的完整命名空间数组
     */
    public function scanController()
    {
        return $this->scanControllerLayerDir($this->controller_path);
    }

    /**
     * 递归扫描控制器目录，扫描到类文件的时候push命名空间到$this->authScanNamespaceList
     * @param string $path 扫描的目标目录
     * @param string $subModule 可空，目标目录的子目录
     * @return array 控制器层下所有类的完整命名空间数组
     */
    private function scanControllerLayerDir(string $path, string $subModule = '')
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                if (strpos($file, '.php')) {
                    $classFileName = substr($file, 0, -4);
                    $module = $subModule ? $subModule . '\\' : '';
                    $completeNamespace = $this->controller_namespace . $module . $classFileName;
                    array_push($this->authScanNamespaceList, $completeNamespace);
                } else {
                    $ds = PHP_OS === 'WINNT' ? '\\' : DIRECTORY_SEPARATOR;
                    $subDir = $path . $ds . $file;
                    $this->scanControllerLayerDir($subDir, $file);
                }
            }
        }
        return $this->authScanNamespaceList;
    }
}