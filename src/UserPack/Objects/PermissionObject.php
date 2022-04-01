<?php

namespace Tivins\UserPack\Objects;

use Tivins\Database\Map\DBOAccess;
use Tivins\Database\Map\DBObject;

class PermissionObject extends DBObject
{

    #[DBOAccess(DBOAccess::PKEY)]
    protected int    $pid     = 0;
    #[DBOAccess(DBOAccess::UNIQ)]
    protected string $name    = '';
    #[DBOAccess]
    protected int    $created = 0;
    #[DBOAccess]
    protected ?int   $deleted = null;

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return static
     */
    public function setPid(int $pid): static
    {
        $this->pid = $pid;
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
     * @return int|null
     */
    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    /**
     * @param int|null $deleted
     * @return static
     */
    public function setDeleted(?int $deleted): static
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return string
     * @todo Make dynamic.
     */
    public function getTableName(): string
    {
        return 'users_permissions';
    }
}