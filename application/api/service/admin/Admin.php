<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:09 下午
 */

namespace app\api\service\admin;


use app\api\model\admin\LinGroup as LinGroupModel;
use app\api\model\admin\LinPermission as LinPermissionModel;
use app\api\model\admin\LinUser as LinUserModel;
use app\api\model\admin\LinUserGroup as LinUserGroupModel;
use app\api\model\admin\LinUserIdentity as LinUserIdentityModel;
use app\lib\authenticator\PermissionScan;
use app\lib\enum\GroupLevelEnum;
use app\lib\enum\MountTypeEnum;
use app\lib\exception\NotFoundException;
use app\lib\exception\OperationException;
use app\lib\exception\token\ForbiddenException;
use LinCmsTp5\exception\ParameterException;
use PDOStatement;
use ReflectionException;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\model\Collection;

class Admin
{
    /**
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws ReflectionException
     */
    public static function getAllPermissions(): array
    {
        $permissionList = (new PermissionScan())->run();
        foreach ($permissionList as $permission) {
            $model = LinPermissionModel::where('name', $permission['name'])
                ->where('module', $permission['module'])
                ->find();
            if (!$model) {
                self::createPermission($permission['name'], $permission['module']);
            }
        }

        $permissions = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
            ->select()->toArray();
        $result = [];
        foreach ($permissions as $permission) {
            $result[$permission['module']][] = $permission;
        }
        return $result;
    }

    /**
     * @param int $page
     * @param int $count
     * @param int $groupId
     * @return array
     * @throws ParameterException
     */
    public static function getUsers(int $page, int $count, int $groupId = null): array
    {
        list($start, $count) = paginate($count, $page);
        $params = $groupId ? ['group_id' => $groupId] : [];
        $users = LinUserModel::getUsers($start, $count, $params);

        return [
            'items' => $users['userList'],
            'count' => $count,
            'page' => $page,
            'total' => $users['total']
        ];
    }

    /**
     * @param int $uid
     * @param string $newPassword
     * @throws NotFoundException
     */
    public static function changeUserPassword(int $uid, string $newPassword): void
    {
        $user = LinUserModel::get($uid);
        if (!$user) {
            throw new NotFoundException();
        }

        LinUserIdentityModel::resetPassword($user, $newPassword);

    }

    /**
     * @param int $uid
     * @throws NotFoundException
     * @throws OperationException
     */
    public static function deleteUser(int $uid): void
    {
        $user = LinUserModel::get($uid, 'identity');
        if (!$user) {
            throw new NotFoundException();
        }

        Db::startTrans();
        try {
            $user->groups()->detach();
            $user->together('identity')->delete();
            Db::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw new OperationException(['msg' => "删除用户失败"]);
        }
    }

    /**
     * @param int $uid
     * @param array $groupIds
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws OperationException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function updateUserInfo(int $uid, array $groupIds): void
    {
        $user = LinUserModel::get($uid);
        if (!$user) {
            throw new NotFoundException();
        }

        $userGroupIds = LinUserGroupModel::where('user_id', $uid)->column('group_id');
        $isAdmin = LinGroupModel::where('level', GroupLevelEnum::ROOT)
            ->whereIn('id', $userGroupIds)
            ->find();
        if ($isAdmin) {
            throw new ForbiddenException(['code' => 10078, 'msg' => '不允许调整root分组信息']);
        }

        foreach ($userGroupIds as $groupId) {
            $group = LinGroupModel::get($groupId);
            if ($group['level'] === GroupLevelEnum::ROOT) {
                throw new ForbiddenException(['code' => 10073, 'msg' => '不允许添加用户到root分组']);
            }

            if (!$group) {
                throw new NotFoundException(['code' => 10077]);
            }
        }

        Db::startTrans();
        try {
            $user->groups()->detach();
            $user->groups()->attach($groupIds);
            Db::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw new OperationException(['msg' => "更新用户分组失败"]);
        }
    }

    /**
     * @return array|PDOStatement|string|\think\Collection|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     */
    public static function getAllGroups()
    {
        $groups = LinGroupModel::where('level', '<>', GroupLevelEnum::ROOT)->select();
        if ($groups->isEmpty()) {
            throw new NotFoundException();
        }
        return $groups;
    }

    /**
     * @param int $id
     * @return \think\db\Query
     * @throws DbException
     * @throws NotFoundException
     */
    public static function getGroup(int $id)
    {
        $group = LinGroupModel::where('level', '<>', GroupLevelEnum::ROOT)
            ->get($id, 'permissions');
        if (!$group) {
            throw new NotFoundException();
        }

        return $group;
    }

    /**
     * @param string $name
     * @param string $info
     * @param array $permissionIds
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     * @throws OperationException
     */
    public static function createGroup(string $name, string $info, array $permissionIds): int
    {
        $isExist = LinGroupModel::where('name', $name)->find();
        if ($isExist) {
            throw new OperationException(['msg' => '分组名已存在']);
        }

        foreach ($permissionIds as $permissionId) {
            $permission = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
                ->get($permissionId);
            if (!$permission) {
                throw new NotFoundException(['error_code' => 10231, 'msg' => '分配了不存在的权限']);
            }
        }

        Db::startTrans();
        try {
            $group = LinGroupModel::create(['name' => $name, 'info' => $info], true);
            $group->permissions()->saveAll($permissionIds);
            Db::commit();
            return $group->getAttr('id');
        } catch (\Exception $ex) {
            Db::rollback();
            throw new OperationException(['msg' => "新增分组失败:{$ex->getMessage()}"]);
        }

    }

    /**
     * @param int $id
     * @param string $name
     * @param string $info
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     */
    public static function updateGroup(int $id, string $name, string $info): int
    {
        $group = LinGroupModel::where('level', '<>', GroupLevelEnum::ROOT)
            ->find($id);

        if (!$group) {
            throw new NotFoundException();
        }

        return $group->save(['name' => $name, 'info' => $info]);
    }

    /**
     * @param int $id
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws OperationException
     */
    public static function deleteGroup(int $id): void
    {
        $group = LinGroupModel::find($id);

        if (!$group) {
            throw new NotFoundException();
        }

        if ($group->getAttr('level') === GroupLevelEnum::ROOT) {
            throw new ForbiddenException(['msg' => '不允许删除root分组']);
        }

        if ($group->getAttr('level') === GroupLevelEnum::GUEST) {
            throw new ForbiddenException(['msg' => '不允许删除guest分组']);
        }

        Db::startTrans();
        try {
            $group->permissions()->detach();
            $group->users()->detach();
            Db::commit();
        } catch (Exception $ex) {
            Db::rollback();
            throw new OperationException(['msg' => "删除分组失败:{$ex->getMessage()}"]);
        }
    }

    /**
     * @param int $id
     * @param array $permissionIds
     * @throws DbException
     * @throws NotFoundException
     * @throws OperationException
     */
    public static function dispatchPermissions(int $id, array $permissionIds)
    {
        $group = LinGroupModel::where('level', '<>', GroupLevelEnum::ROOT)
            ->get($id);
        if (!$group) {
            throw new NotFoundException();
        }

        foreach ($permissionIds as $permissionId) {
            $permission = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
                ->get($permissionId);
            if (!$permission) {
                throw new NotFoundException(['error_code' => 10231, 'msg' => '分配了不存在的权限']);
            }
        }

        try {
            $group->permissions()->attach($permissionIds);
        } catch (Exception $ex) {
            throw new OperationException(['msg' => '权限分配失败']);
        }
    }

    /**
     * @param int $id
     * @param array $permissionIds
     * @return int
     * @throws DbException
     * @throws NotFoundException
     */
    public static function removePermissions(int $id, array $permissionIds): int
    {
        $group = LinGroupModel::where('level', '<>', GroupLevelEnum::ROOT)
            ->get($id);
        if (!$group) {
            throw new NotFoundException();
        }

        foreach ($permissionIds as $permissionId) {
            $permission = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
                ->get($permissionId);
            if (!$permission) {
                throw new NotFoundException(['error_code' => 10231, 'msg' => '分配了不存在的权限']);
            }
        }

        return $group->permissions()->detach($permissionIds);
    }

    public static function createPermission(string $name, string $module): LinPermissionModel
    {
        return LinPermissionModel::create(['name' => $name, 'module' => $module, 'mount' => 1]);
    }
}