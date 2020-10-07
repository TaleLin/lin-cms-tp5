<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/8/14
 * Time: 11:15 下午
 */

namespace app\lib\enum;


class PermissionLevelEnum
{
    const LOGIN_REQUIRED = 'loginRequired';

    const GROUP_REQUIRED = 'groupRequired';

    const ADMIN_REQUIRED = 'adminRequired';
}