<?php

namespace Tivins\UserPack;

use Tivins\Core\Http\Status as HTTPStatus;
use Tivins\Database\Conditions;
use Tivins\Database\CreateQuery;
use Tivins\Database\Database;
use Tivins\Database\Exceptions\ConditionException;

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
            ->addInteger('deleted', null, true)
            ;
        $this->alterCreateTable($query);
        $query->execute();
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

    public function exists(string $name, string $email): bool
    {
        return $this->getByCondition(
            $this->db->or()
                ->condition('name', $name)
                ->condition('email', $email)
        ) !== false;
    }

    public function createUser(string $name, string $email, string $clearPassword): int
    {
        try {
            $this->db->insert($this->tableName)
                ->fields([
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($clearPassword, PASSWORD_DEFAULT),
                    'created' => time(),
                ])
                ->execute();
            return $this->db->lastId();
        } catch (\Exception $ex) {
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
        } catch (\Exception $e) {
            return false;
        }
        $userID = $decoded?->data?->uid;
        if (!$userID) {
            return false;
        }
        return $this->getById($userID);
    }

    public function getByCondition(Conditions $conds): object|false
    {
        return $this->db->select($this->tableName, 't')
            ->addFields('t')
            ->condition($conds)
            ->execute()
            ->fetch() ?? false;
    }

    public function getById(int $id): object|false
    {
        return $this->getByCondition($this->db->and()->condition('id', $id)->isNull('deleted'));
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
}