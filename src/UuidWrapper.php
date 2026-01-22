<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid;

use DateTimeImmutable;
use LogicException;
use Stringable;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV6;
use Symfony\Component\Uid\UuidV7;

/**
 * Decorator for Symfony's AbstractUid that provides
 * backward-compatible method names from Ramsey UUID.
 */
class UuidWrapper implements Stringable
{
    public function __construct(private readonly AbstractUid $uid)
    {
    }

    /**
     * Get the underlying Symfony UID instance.
     */
    public function unwrap(): AbstractUid
    {
        return $this->uid;
    }

    //
    // Ramsey UUID compatible methods (aliases)
    //

    /**
     * @deprecated Use toRfc4122() instead
     */
    public function toString(): string
    {
        return $this->uid->toRfc4122();
    }

    /**
     * @deprecated Use toBinary() instead
     */
    public function getBytes(): string
    {
        return $this->uid->toBinary();
    }

    /**
     * @deprecated Use toHex() instead
     */
    public function getHex(): string
    {
        return $this->uid->toHex();
    }

    //
    // Symfony UID methods (delegated)
    //

    public function toRfc4122(): string
    {
        return $this->uid->toRfc4122();
    }

    public function toBinary(): string
    {
        return $this->uid->toBinary();
    }

    public function toBase58(): string
    {
        return $this->uid->toBase58();
    }

    public function toBase32(): string
    {
        return $this->uid->toBase32();
    }

    public function toHex(): string
    {
        return $this->uid->toHex();
    }

    public function equals(mixed $other): bool
    {
        if ($other instanceof self) {
            $other = $other->uid;
        }

        return $this->uid->equals($other);
    }

    public function compare(mixed $other): int
    {
        if ($other instanceof self) {
            $other = $other->uid;
        }

        return $this->uid->compare($other);
    }

    /**
     * Get the datetime from time-based UUIDs (v1, v6, v7) or ULIDs.
     *
     * @throws LogicException If the UUID is not time-based
     */
    public function getDateTime(): DateTimeImmutable
    {
        /** @var Ulid|UuidV1|UuidV6|UuidV7 $uid */
        $uid = $this->uid;

        return $uid->getDateTime();
    }

    public function __toString(): string
    {
        return $this->uid->toRfc4122();
    }
}
