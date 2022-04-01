<?php

namespace Tivins\UserPack\Objects;

use Tivins\Database\Map\DBOManager;

class Permission extends PermissionObject
{
    public function getRoles(): array
    {
        return Role::loadCollection(
            DBOManager::db()
                ->select('users_roles_perms', 'rp')
                ->innerJoin('users_roles', 'r', 'r.rid = rp.rid')
                ->addFields('r')
                ->isEqual('rp.pid', $this->getPid())
                ->execute()
        );
    }
}
