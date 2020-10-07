<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use LinCmsTp5\exception\ParameterException;
use think\response\Json;

/**
 * 统一响应包装函数
 * @param $code
 * @param $errorCode
 * @param $data
 * @param $msg
 * @return Json
 */
function writeJson($code, $data, $msg = 'ok', $errorCode = 0)
{
    $data = [
        'code' => $errorCode,
        'result' => $data,
        'message' => $msg
    ];
    return json($data, $code);
}

/**
 * 分页参数处理函数
 * @param int $count
 * @param int $page
 * @return array
 * @throws ParameterException
 */
function paginate(int $count = 10, int $page = 0)
{
    // $count = intval(Request::get('count', $count));
    // $start = intval(Request::get('page', $page));
    // $page = $start;
    $count = $count >= 15 ? 15 : $count;
    $start = $page * $count;

    if ($start < 0 || $count < 0) throw new ParameterException();

    return [$start, $count];
}

/**
 * 权限数组格式化函数
 * @param array $permissions
 * @return array
 */
function formatPermissions(array $permissions)
{
    $groupPermission = [];
    foreach ($permissions as $permission) {
        $item = [
            'permission' => $permission['name'],
            'module' => $permission['module']
        ];
        $groupPermission[$permission['module']][] = $item;
    }
    $result = [];
    foreach ($groupPermission as $key => $item) {
        array_push($result, [$key => $item]);
    }

    return $result;
}