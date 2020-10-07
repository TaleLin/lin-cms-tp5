<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 9:54
 */

namespace app\lib\authenticator;

use app\api\service\token\LoginToken;
use app\lib\enum\PermissionLevelEnum;
use app\lib\exception\token\DeployException;
use Exception;
use ReflectionClass;
use ReflectionException;
use think\Request;
use WangYu\Reflex;

class Authenticator
{

    private $parsedClass;

    public function __construct(Request $request)
    {
        // 获取当前请求的控制层
        $controller = $request->controller();
        // 控制层下有二级目录，需要解析下。如controller/cms/Admin，获取到的是Cms.Admin
        $controllerPath = explode('.', $controller);
        // 获取当前请求的方法
        $action = $request->action();
        // 反射获取当前请求的控制器类
        $class = new ReflectionClass('app\\api\\controller\\' . strtolower($controllerPath[0]) . '\\' . $controllerPath[1]);
        $this->parsedClass = (new Reflex($class->newInstance()))->setMethod($action);
    }

    /**
     * 入口方法
     * @return bool
     * @throws DeployException
     * @throws ReflectionException
     */
    public function check(): bool
    {
        //判断是否开启加载文件函数注释
        if (ini_get('opcache.save_comments') === '0' || ini_get('opcache.save_comments') === '') {
            throw new DeployException();
        }
        // 获取方法权限控制等级
        $actionPermissionLevel = $this->actionAuthorityLevel();
        // 没有等级标识，直接通过
        if (!$actionPermissionLevel) {
            return true;
        }

        // 执行校验并返回校验结果
        return $this->execute($actionPermissionLevel);

    }

    /**
     * 执行各权限等级校验
     * @param string $actionPermissionLevel
     * @return bool
     * @throws ReflectionException
     */
    public function execute(string $actionPermissionLevel): bool
    {
        // 账户信息，包含所拥有的权限列表
        $userInfo = $this->getUserInfo();
        //账户属于超级管理员，直接通过
        if ($userInfo['admin'] === true) return true;
        $actionPermissionName = $this->actionPermission();

        return AuthenticatorExecutorFactory::getInstance($actionPermissionLevel)->handle($userInfo, $actionPermissionName);

    }

    /**
     * 获取接口权限等级注解
     * @return string
     * @throws Exception
     */
    protected function actionAuthorityLevel(): string
    {
        $permissionLevel = null;

        if ($this->parsedClass->isExist(PermissionLevelEnum::LOGIN_REQUIRED)) {
            $permissionLevel = PermissionLevelEnum::LOGIN_REQUIRED;
            return $permissionLevel;
        }

        if ($this->parsedClass->isExist(PermissionLevelEnum::GROUP_REQUIRED)) {
            $permissionLevel = PermissionLevelEnum::GROUP_REQUIRED;
            return $permissionLevel;
        }
        if ($this->parsedClass->isExist(PermissionLevelEnum::ADMIN_REQUIRED)) {
            $permissionLevel = PermissionLevelEnum::ADMIN_REQUIRED;
            return $permissionLevel;
        }
        return '';
    }

    protected function getUserInfo(): array
    {
        return LoginToken::getInstance()->getTokenExtend();
    }

    /**
     * 获取接口权限注解内容
     * @return string
     * @throws Exception
     */
    protected function actionPermission(): string
    {
        $actionAuthContent = $this->parsedClass->get('permission');

        $actionAuthContent = empty($actionAuthContent) ? '' : $actionAuthContent[0] . '/' . $actionAuthContent[1];
        return $actionAuthContent;
    }
}
