<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

/**
 * @template T Value type
 */
abstract readonly class AbstractValueObject
{
    /**
     * @return T
     */
    abstract public function value(): mixed;

    /**
     * @param self<T> $other
     */
    public function equals(self $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        $value = $this->value();

        assert(
            is_null($value)
            || is_scalar($value)
            || $value instanceof \Stringable,
        );

        return (string) ($value ?? '');
    }
}
