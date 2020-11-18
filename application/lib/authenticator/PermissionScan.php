<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 9:59
 */

namespace app\lib\authenticator;


use app\lib\enum\MountTypeEnum;
use Exception;
use ReflectionClass;
use ReflectionException;
use WangYu\Reflex;

class PermissionScan
{
    private $namespaceList;

    public function __construct()
    {
        $this->namespaceList = (new Scan())->scanController();
    }

    /**
     * @throws ReflectionException
     */
    public function run()
    {
        return $this->getPermissionList();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getPermissionList()
    {
        $permissionList = [];
        // 遍历需要解析@permission注解的控制器类
        foreach ($this->namespaceList as $value) {
            // 反射控制器类
            $class = new ReflectionClass($value);
            // 类下面的所有方法的数组
            $methods = $class->getMethods();
            // 类下面所有含有@permission注解的方法的注解内容数组
            $methodPermissionList = $this->getPermissionByMethods($class->newInstance(), $methods);

            if (!empty($methodPermissionList)) {
                // 插入类权限数组
                if (empty($permissionList)) {
                    $permissionList = $methodPermissionList;
                } else {
                    $permissionList = array_merge($permissionList, $methodPermissionList);
                }
            }
        }
        return $permissionList;
    }

    /**
     * @param $class
     * @param $methods
     * @param string $annotationField
     * @return array
     * @throws Exception
     */
    private function getPermissionByMethods($class, $methods, $annotationField = 'permission')
    {
        $data = [];
        $re = new Reflex($class);
        foreach ($methods as $value) {
            $re->setMethod($value->name);
            $permissionAnnotationArray = $re->get($annotationField);

            if (!empty($permissionAnnotationArray) && !in_array('hidden', $permissionAnnotationArray)) {
                $permission = $this->handleAnnotation($permissionAnnotationArray);
                array_push($data, $permission);
            }
        }

        return $data;
    }

    public function handleAnnotation(array $annotation)
    {
        return [
            'name' => $annotation[0],
            'module' => $annotation[1],
            'mount' => MountTypeEnum::MOUNT
        ];
    }
}