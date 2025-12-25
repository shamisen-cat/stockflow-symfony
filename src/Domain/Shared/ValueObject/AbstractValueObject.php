<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

/**
 * 値オブジェクトの基底クラス
 *
 * @template T 値の型
 */
abstract readonly class AbstractValueObject
{
    /**
     * 値を取得する。
     *
     * @return T 値
     */
    abstract public function value(): mixed;

    /**
     * 値の文字列表現を取得する。
     *
     * @return string 値の文字列表現
     */
    public function __toString(): string
    {
        $value = $this->value();
        assert(is_null($value) || is_scalar($value) || $value instanceof \Stringable);

        return (string) ($value ?? '');
    }

    /**
     * 値オブジェクトの等価性を比較する。
     *
     * @param static $other 値オブジェクト
     */
    public function equals(self $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->value() === $other->value();
    }
}
