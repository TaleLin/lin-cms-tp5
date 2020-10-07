<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/8/14
 * Time: 11:29 下午
 */

namespace app\lib\authenticator\executor\impl;


use app\lib\authenticator\executor\IExecutor;

class GroupRequireExecutorImpl implements IExecutor
{

    public function handle(array $userInfo = null, string $permissionName = ''): bool
    {
        if (empty($userInfo['permissions'])) return false;

        $permissionArray = [];
        foreach ($userInfo['permissions'] as $permissionGroup) {
            foreach ($permissionGroup as $group) {
                foreach ($group as $permission) {

                    $permission = (array)$permission;
                    $permissionTag = $permission['permission'] . '/' . $permission['module'];
                    array_push($permissionArray, $permissionTag);
                }
            }
        }
        return in_array($permissionName, $permissionArray);
    }
}