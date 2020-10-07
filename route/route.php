<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::get('v1/test', 'api/v1.Test/index');

Route::group('', function () {
    Route::group('cms', function () {
        // 账户相关接口分组
        Route::group('user', function () {
            // 登陆接口
            Route::post('login', 'api/cms.User/userLogin');
            // 刷新令牌
            Route::get('refresh', 'api/cms.User/refreshToken');
            // 查询自己拥有的权限
            Route::get('permissions', 'api/cms.User/getAllowedApis');
            // 注册一个用户
            Route::post('register', 'api/cms.User/register');
            // 查询自己信息
            Route::get('information', 'api/cms.User/getInformation');
            // 用户更新信息
            Route::put('', 'api/cms.User/update');
            // 修改自己密码
            Route::put('change_password', 'api/cms.User/changePassword');
        });
        // 管理类接口
        Route::group('admin', function () {
            // 查询所有可分配的权限
            Route::get('permission', 'api/cms.Admin/getAllPermissions');
            // 查询所有用户
            Route::get('users', 'api/cms.Admin/getAdminUsers');
            // 修改用户密码
            Route::put('user/:id/password', 'api/cms.Admin/changeUserPassword');
            // 删除用户
            Route::delete('user/:id', 'api/cms.Admin/deleteUser');
            // 更新用户信息
            Route::put('user/:id', 'api/cms.Admin/updateUser');
            // 查询所有权限组
            Route::get('group/all', 'api/cms.Admin/getGroupAll');
            // 新增权限组
            Route::post('group', 'api/cms.Admin/createGroup');
            // 查询指定分组及其权限
            Route::get('group/:id', 'api/cms.Admin/getGroup');
            // 更新一个权限组
            Route::put('group/:id', 'api/cms.Admin/updateGroup');
            // 删除一个分组
            Route::delete('group/:id', 'api/cms.Admin/deleteGroup');
            // 删除多个权限
            Route::post('permission/remove', 'api/cms.Admin/removePermissions');
            // 分配多个权限
            Route::post('permission/dispatch/batch', 'api/cms.Admin/dispatchPermissions');

        });
        // 日志类接口
        Route::group('log', function () {
            Route::get('', 'api/cms.Log/getLogs');
            Route::get('users', 'api/cms.Log/getUsers');
            Route::get('search', 'api/cms.Log/getUserLogs');
        });
        //上传文件类接口
        Route::post('file', 'api/cms.File/postFile');
    });
    Route::group('v1', function () {
        Route::group('book', function () {
            // 查询所有图书
            Route::get('', 'api/v1.Book/getBooks');
            // 新建图书
            Route::post('', 'api/v1.Book/create');
            // 查询指定bid的图书
            Route::get(':bid', 'api/v1.Book/getBook');
            // 搜索图书

            // 更新图书
            Route::put(':bid', 'api/v1.Book/update');
            // 删除图书
            Route::delete(':bid', 'api/v1.Book/delete');
        });

    });
})->middleware(['Authentication', 'ReflexValidate'])->allowCrossDomain();

