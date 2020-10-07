<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/8/14
 * Time: 11:29 下午
 */

namespace app\lib\authenticator\executor\impl;


use app\lib\authenticator\executor\IExecutor;

class AdminRequireExecutorImpl implements IExecutor
{

    public function handle(array $userInfo = null, string $permissionName = ''): bool
    {
        return $userInfo['admin'];
    }
}