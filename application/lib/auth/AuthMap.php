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
            $methodAuthList = $this->getMethodsDoc($class->newInstance(), $methods);
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
     * @throws \Exception
     */
    private function getMethodsDoc($class, $array)
    {
        $data = [];
        foreach ($array as $value) {
            $re = new Reflex($class);
            $re->setMethod($value->name);
            $authAnnotation = $re->get('auth');
            if ($authAnnotation === null) {
                $authAnnotation = [];
            }
            $authAnnotation = $this->handleAnnotation($authAnnotation);
            if (!empty($authAnnotation)) {
                array_push($data, $authAnnotation);
            }
        }
        // 根据权限所属模型对注解内容数组进行分组
        $methodsAuthGroup = $this->authListGroup($data);
        return $methodsAuthGroup;
    }

    /**
     * @param $class
     * @param $method
     * @return string
     * @throws \Exception
     */
    public function getMethodAuthName($class, $method)
    {
        $re = new Reflex($class);
        $re->setMethod($method);
        $authAnnotation = $re->get('auth');
        $authName = empty($authAnnotation) ? '' : $authAnnotation[0];
        return $authName;
    }

    public function handleAnnotation(array $annotation)
    {
        if (empty($annotation) || in_array('hidden', $annotation)) {
            return [];
        }

        return [
            $annotation[1] => [$annotation[0] => ['']]
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