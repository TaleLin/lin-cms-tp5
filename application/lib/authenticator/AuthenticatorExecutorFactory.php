<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/8/14
 * Time: 11:21 下午
 */

namespace app\lib\authenticator;


use app\lib\authenticator\executor\IExecutor;
use app\lib\authenticator\executor\impl\AdminRequireExecutorImpl;
use app\lib\authenticator\executor\impl\GroupRequireExecutorImpl;
use app\lib\authenticator\executor\impl\LoginRequireExecutorImpl;
use app\lib\enum\PermissionLevelEnum;

class AuthenticatorExecutorFactory
{
    public static function getInstance(string $level): IExecutor
    {
        $instance = null;
        switch ($level) {
            case PermissionLevelEnum::LOGIN_REQUIRED:
                $instance = new LoginRequireExecutorImpl();
                break;
            case PermissionLevelEnum::GROUP_REQUIRED:
                $instance = new GroupRequireExecutorImpl();
                break;
            case PermissionLevelEnum::ADMIN_REQUIRED:
                $instance = new AdminRequireExecutorImpl();
                break;
        }
        return $instance;
    }
}