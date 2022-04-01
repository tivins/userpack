<?php

namespace Tivins\UserPack\Objects;

use Tivins\Database\Map\DBOAccess;
use Tivins\Database\Map\DBObject;

class RoleObject extends DBObject
{
    #[DBOAccess(DBOAccess::PKEY)]
    protected int $rid = 0;
    #[DBOAccess(DBOAccess::UNIQ)]
    protected string $name = '';
    #[DBOAccess]
    protected int $created = 0;
    #[DBOAccess]
    protected ?int $deleted = null;

    /**
     * @return string
     * @todo make it dynamic
     */
    public function getTableName(): string
    {
        return 'users_roles';
    }

    /**
     * @return int
     */
    public function getRid(): int
    {
        return $this->rid;
    }

    /**
     * @param int $rid
     * @return static
     */
    public function setRid(int $rid): static
    {
        $this->rid = $rid;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int $created
     * @return static
     */
    public function setCreated(int $created): static
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return static
     */
    public function setDeleted(int $deleted): static
    {
        $this->deleted = $deleted;
        return $this;
    }
}
