<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/16
 * Time: 11:21
 */

namespace app\api\controller\cms;

use app\api\service\admin\Admin as AdminService;
use app\lib\exception\NotFoundException;
use app\lib\exception\OperationException;
use app\lib\exception\token\ForbiddenException;
use LinCmsTp5\exception\ParameterException;
use PDOStatement;
use ReflectionException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\exception\DbException;
use think\facade\Hook;
use think\model\Collection;
use think\Request;
use think\response\Json;

class Admin
{
    /**
     * @adminRequired
     * @permission('查询所有可分配的权限','管理员','hidden')
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws ReflectionException
     */
    public function getAllPermissions()
    {
        return AdminService::getAllPermissions();
    }

    /**
     * @adminRequired
     * @permission('查询所有用户','管理员','hidden')
     * @param Request $request
     * @param('page','分页数','integer')
     * @param('count','分页值','integer')
     * @param('group_id','分组id','integer')
     * @return array
     * @throws ParameterException
     */
    public function getAdminUsers(Request $request)
    {
        $page = $request->get('page/d', 0);
        $count = $request->get('count/d', 10);
        $groupId = $request->get('group_id/d');

        return AdminService::getUsers($page, $count, $groupId);
    }

    /**
     * @adminRequired
     * @permission('修改用户密码','管理员','hidden')
     * @validate('ResetPasswordValidator')
     * @param Request $request
     * @param $id
     * @return Json
     * @throws NotFoundException
     */
    public function changeUserPassword(Request $request, $id)
    {
        $newPassword = $request->put('new_password');
        AdminService::changeUserPassword($id, $newPassword);
        Hook::listen('logger', "修改了用户ID为{$id}的密码");

        return writeJson(200, null, '修改成功', 4);
    }

    /**
     * @adminRequired
     * @permission('删除用户','管理员','hidden')
     * @param int $id
     * @param('id','用户id','require|integer')
     * @return Json
     * @throws NotFoundException
     * @throws OperationException
     */
    public function deleteUser(int $id)
    {
        AdminService::deleteUser($id);
        Hook::listen('logger', "删除了用户ID为：{$id}的用户");
        return writeJson(201, $id, '删除用户成功', 5);
    }

    /**
     * @adminRequired
     * @permission('管理员更新用户信息','管理员','hidden')
     * @param Request $request
     * @param('id','用户id','require|integer')
     * @param('group_ids','分组id','require|array|min:1')
     * @return Json
     * @throws NotFoundException
     * @throws OperationException
     * @throws ForbiddenException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function updateUser(Request $request, $id)
    {
        $groupIds = $request->put('group_ids');
        AdminService::updateUserInfo($id, $groupIds);

        Hook::listen('logger', "更新了用户：{$id}的所属分组");
        return writeJson(201, $id, '更新用户成功', 6);
    }

    /**
     * @adminRequired
     * @permission('查询所有分组','管理员','hidden')
     * @return array|PDOStatement|string|\think\Collection|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     */
    public function getGroupAll()
    {
        return AdminService::getAllGroups();
    }

    /**
     * @adminRequired
     * @permission('查询一个权限组及其权限','管理员','hidden')
     * @param int $id
     * @param('id','分组id','require|integer')
     * @return Query
     * @throws DbException
     * @throws NotFoundException
     */
    public function getGroup(int $id)
    {
        return AdminService::getGroup($id);
    }

    /**
     * @adminRequired
     * @permission('新建一个权限组','管理员','hidden')
     * @param Request $request
     * @param('name','分组名字','require')
     * @param('permission_ids','权限id','require|array|min:1')
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     * @throws OperationException
     */
    public function createGroup(Request $request)
    {
        $name = $request->post('name');
        $info = $request->post('info');
        $permissionIds = $request->post('permission_ids');

        $groupId = AdminService::createGroup($name, $info, $permissionIds);

        Hook::listen('logger', "创建了分组：{$name}");
        return writeJson(201, $groupId, '新增分组成功', 15);
    }

    /**
     * @adminRequired
     * @permission('更新一个权限组','管理员','hidden')
     * @param Request $request
     * @param int $id
     * @param('id','分组id','require|integer')
     * @param('info','分组信息','require')
     * @param('name','分组名字','require')
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     */
    public function updateGroup(Request $request, int $id)
    {
        $name = $request->put('name');
        $info = $request->put('info');

        $res = AdminService::updateGroup($id, $name, $info);

        Hook::listen('logger', "更新了id为{$id}的分组");
        return writeJson(200, $res, '更新分组信息成功', 7);
    }

    /**
     * @adminRequired
     * @permission('更新一个权限组','管理员','hidden')
     * @param int $id
     * @param('id','分组id','require|integer')
     * @return Json
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws OperationException
     */
    public function deleteGroup(int $id)
    {
        AdminService::deleteGroup($id);

        Hook::listen('logger', "删除了id为{$id}的分组");
        return writeJson(200, null, '删除分组成功', 8);
    }

    /**
     * @adminRequired
     * @permission('分配多个权限','管理员','hidden')
     * @param Request $request
     * @param('group_id','分组id','require|integer')
     * @param('permission_ids','权限id','require|array|min:1')
     * @return Json
     * @throws DbException
     * @throws NotFoundException
     * @throws OperationException
     */
    public function dispatchPermissions(Request $request)
    {
        $groupId = $request->post('group_id');
        $permissionIds = $request->post('permission_ids');

        AdminService::dispatchPermissions($groupId, $permissionIds);

        Hook::listen('logger', "修改了分组ID为{$groupId}的权限");
        return writeJson(200, null, '分配权限成功', 9);
    }

    /**
     * @adminRequired
     * @permission('删除多个权限','管理员','hidden')
     * @param Request $request
     * @param('group_id','分组id','require|integer')
     * @param('permission_ids','权限id','require|array|min:1')
     * @return Json
     * @throws DbException
     * @throws NotFoundException
     */
    public function removePermissions(Request $request)
    {
        $groupId = $request->post('group_id');
        $permissionIds = $request->post('permission_ids');

        $deleted = AdminService::removePermissions($groupId, $permissionIds);

        Hook::listen('logger', "修改了分组ID为{$groupId}的权限");
        return writeJson(200, $deleted, '删除权限成功', 10);
    }
}