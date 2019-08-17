<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 9:59
 */

namespace app\lib\auth;


use WangYu\Reflex;

class AuthMap
{
    private $authScanNamespaceList;

    public function __construct()
    {
        $this->authScanNamespaceList = (new Scan())->scanController();
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \WangYu\exception\ReflexException
     */
    public function run()
    {
        $authList = $this->geAuthList();
        return $authList;
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \WangYu\exception\ReflexException
     */
    private function geAuthList()
    {
        $authList = [];
        // 遍历需要解析@auth注解的控制器类
        foreach ($this->authScanNamespaceList as $value) {
            // 反射控制器类
            $class = new \ReflectionClass($value);
            // 类下面的所有方法的数组
            $methods = $class->getMethods();
            // 类下面所有含有@auth注解的方法的注解内容数组
            $methodAuthList = $this->getMethodsDoc($class, $methods);
            // 插入类权限数组
            array_push($authList, $methodAuthList);
        }
        // 类权限数组分组
        $authListGroup = $this->classAuthListGroup($authList);
        return $authListGroup;

    }

    /**
     * @param $class
     * @param $array
     * @return array
     * @throws \WangYu\exception\ReflexException
     */
    private function getMethodsDoc($class, $array)
    {
        $data = [];
        foreach ($array as $value) {
            $reflex = new Reflex($class, $value->name);
            $authAnnotation = $reflex->get('auth');
            $authAnnotation = $this->handleAnnotation($authAnnotation);
            if (!empty($authAnnotation)) {
                array_push($data, $authAnnotation);
            }
        }
        // 根据权限所属模型对注解内容数组进行分组
        $methodsAuthGroup = $this->authListGroup($data);
        return $methodsAuthGroup;
    }

    public function getMethodAuthName($class,$method)
    {
        $authAnnotation = (new Reflex($class, $method))->get('auth');
        $authName =  empty($authAnnotation) ? '' : $authAnnotation[0][0];
        return $authName;
    }

    public function handleAnnotation(array $annotation)
    {
        if (empty($annotation[0]) || in_array('hidden', $annotation[0])) {
            return [];
        }

        return [
            $annotation[0][1] => [$annotation[0][0] => ['']]
        ];
    }

    private function authListGroup($authList)
    {
        $result = [];
        foreach ($authList as $key => $value) {
            foreach ($value as $k => $v) {
                $result[$k] = [];
            }
        }

        foreach ($authList as $key => $value) {
            foreach ($value as $k => $v) {
                array_push($result[$k], $v);
            }
        }

        return $result;
    }

    private function classAuthListGroup($authList)
    {
        $result = [];
        foreach ($authList as $key => $value) {
            foreach ($value as $k => $v) {
                $result[$k] = [];
            }
        }

        foreach ($authList as $key => $value) {
            foreach ($value as $k => $v) {
                foreach ($v as $item) {
                    $arrayKeys = array_keys($item);
                    $arrayValues = array_values($item);
                    $result[$k][$arrayKeys[0]] = $arrayValues[0];
                }
            }
        }

        return $result;
    }
}