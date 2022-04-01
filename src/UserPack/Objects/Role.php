<?php

namespace Tivins\UserPack\Objects;

use Tivins\Database\Map\DBOManager;

class Role extends RoleObject
{
    /**
     * @param Permission $perm
     * @return int
     */
    public function addPermission(Permission $perm): int
    {
        DBOManager::db()->insert('users_roles_perms')
            ->fields([
                'rid' => $this->getRid(),
                'pid' => $perm->getPid(),
            ])
            ->execute();
        return DBOManager::db()->lastId();
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return Permission::loadCollection(DBOManager::db()->select('users_roles_perms', 'rp')
            ->innerJoin('users_permissions', 'p', 'p.pid = rp.pid')
            ->addFields('p')
            ->isEqual('rp.rid', $this->getRid())
            ->execute());

    }

    /**
     * @param int $userID
     * @return int
     */
    public function addUser(int $userID): int
    {
        DBOManager::db()->insert('users_users_roles')
            ->fields([
                'uid' => $userID,
                'rid' => $this->getRid(),
            ])
            ->execute();
        return DBOManager::db()->lastId();
    }
}

