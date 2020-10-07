<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/8/14
 * Time: 11:27 下午
 */

namespace app\lib\authenticator\executor;


interface IExecutor
{
    public function handle(array $userInfo = null, string $permissionName = ''): bool;
}