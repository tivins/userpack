<?php

namespace Tivins\UserPack;

use Exception;
use Tivins\Database\Conditions;
use Tivins\Database\CreateQuery;
use Tivins\Database\Database;
use Tivins\Database\Exceptions\ConditionException;
use Tivins\UserPack\Objects\Permission;
use Tivins\UserPack\Objects\Role;

/*

class MyUserModule extends UserModule
{
    protected string $tableName = 'users';
    public function alterTable(CreateQuery $query): void
    {
    }
}

*/

/**
 *
 */
class UserModule
{
    protected string $tableName = 'users';

    public function __construct(protected Database $db)
    {
    }

    public function install(): static
    {
        $this->db->dropTable($this->tableName);
        $query = $this->db->create($this->tableName)
            ->addAutoIncrement('id')
            ->addString('name')
            ->addString('email')
            ->addString('password')
            ->addUniqueKey(['email'])
            ->addUniqueKey(['name'])
            ->addInteger('created', 0, true)
            ->addInteger('accessed', null, true)
            ->addInteger('updated', null, true)
            ->addInteger('deleted', null, true);
        $this->alterCreateTable($query);
        $query->execute();

        $this->db->dropTable($this->tableName . '_permissions');
        $query = $this->db->create($this->tableName . '_permissions')
            ->addAutoIncrement('pid')
            ->addString('name')
            ->addUniqueKey(['name'])
            ->addInteger('created', 0, true)
            ->addInteger('deleted', null, true)
            ->execute();

        $this->db->dropTable($this->tableName . '_roles');
        $query = $this->db->create($this->tableName . '_roles')
            ->addAutoIncrement('rid')
            ->addString('name')
            ->addUniqueKey(['name'])
            ->addInteger('created', 0, true)
            ->addInteger('deleted', null, true)
            ->execute();

        $this->db->dropTable($this->tableName . '_roles_perms');
        $query = $this->db->create($this->tableName . '_roles_perms')
            ->addAutoIncrement('prid')
            ->addPointer('pid')
            ->addPointer('rid')
            ->addUniqueKey(['pid', 'rid'])
            ->execute();

        $this->db->dropTable($this->tableName . '_users_roles');
        $query = $this->db->create($this->tableName . '_users_roles')
            ->addAutoIncrement('urid')
            ->addPointer('uid')
            ->addPointer('rid')
            ->addUniqueKey(['uid', 'rid'])
            ->execute();
        return $this;
    }

    /**
     */
    public function createExample(): static
    {
        $userID  = $this->createUser('admin', 'admin@example.com', 'admin');
        $userID2 = $this->createUser('admin2', 'admin2@example.com', 'admin2');

        $permAccessAdmin        = $this->createPermission('access admin');
        $permAccessTranslations = $this->createPermission('access translations');

        $roleAdmin = $this->createRole('admin');
        $roleAdmin->addPermission($permAccessAdmin);

        $role = $this->createRole('moderator');

        $role = $this->createRole('translator');
        $role->addPermission($permAccessTranslations);
        $role->addPermission($permAccessAdmin);
        $role->addUser($userID);

        $role = $this->createRole('animator');
        $role = $this->createRole('admin_sys');

        $role = new Role($this->db);
        $role->load($this->db->and()->isEqual('name', 'translator'));
        // echo json_encode($role);
        // echo json_encode($role->getPermissions());
        // echo json_encode($permAccessAdmin->getRoles());

        return $this;
    }


    /**
     * This method could be overridden in an extended class. It allows to alter the creation query.
     * @param CreateQuery $query
     * @return void
     */
    public function alterCreateTable(CreateQuery $query): void
    {
    }

    public function exists(string $name, string $email, int $exceptUser = 0): bool
    {
        $conditions = $this->db->and();

        $conditions->nest(
            $this->db->or()
            ->isEqual('name', $name)
            ->isEqual('email', $email)
        );

        if ($exceptUser) {
            $conditions->nest(
                $this->db->and()
                ->isDifferent('id', $exceptUser)
            );
        }
        return $this->getByCondition($conditions) !== false;
    }

    public function getByCondition(Conditions $conditions): object|false
    {
        $query = $this->db->select($this->tableName, 't')
            ->addFields('t')
            ->nest($conditions);
        return $query
                ->execute()
                ->fetch() ?? false;
    }

    public function createUser(string $name, string $email, string $clearPassword): int
    {
        try {
            $this->db->insert($this->tableName)
                ->fields([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => password_hash($clearPassword, PASSWORD_DEFAULT),
                    'created'  => time(),
                ])
                ->execute();
            return $this->db->lastId();
        } catch (Exception) {
            return 0;
        }
    }

    public function getFromHTTPAuthorization(WebToken $webToken): object|false
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return false;
        }
        [, $token] = explode(' ', $_SERVER['HTTP_AUTHORIZATION'], 2) + ['', ''];
        try {
            $decoded = $webToken->decode($token);
        } catch (Exception $e) {
            return false;
        }
        $userID = $decoded?->data?->uid;
        if (!$userID) {
            return false;
        }
        return $this->getById($userID);
    }

    public function getById(int $id): object|false
    {
        return $this->getByCondition($this->db->and()->isEqual('id', $id)->isNull('deleted'));
    }

    public function getByCredentials(string $name, string $clearPassword): object|false
    {
        try {
            $potentialUser = $this->db->select($this->tableName, 't')
                ->addFields('t')
                ->condition('t.name', $name)
                ->execute()
                ->fetch();

            if (!$potentialUser) {
                return false;
            }

            if (!password_verify($clearPassword, $potentialUser->password)) {
                return false;
            }
            return $potentialUser;
        } catch (ConditionException) {
            return false;
        }
    }

    /**
     *
     */
    private function createPermission(string $name): Permission
    {
        return (new Permission())
            ->setName($name)
            ->setCreated(time())
            ->save();
    }

    /**
     *
     */
    private function createRole(string $name): Role
    {
        return (new Role())
            ->setName($name)
            ->setCreated(time())
            ->save();
    }

    public function update(int $id, string $name, string $email): bool
    {
        try {
            $this->db->update($this->tableName)
                ->fields(['name' => $name, 'email' => $email])
                ->isEqual('id', $id)
                ->execute();
            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }
}