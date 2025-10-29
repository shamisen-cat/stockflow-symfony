<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

/**
 * 値オブジェクトの基底クラス
 */
abstract class AbstractValueObject
{
    /**
     * 値を取得する。
     *
     * @return mixed 値
     */
    abstract public function value(): mixed;

    /**
     * 値の文字列表現を取得する。
     *
     * @return string 値の文字列表現
     */
    public function __toString(): string
    {
        return (string) $this->value();
    }

    /**
     * 指定された値オブジェクトとの等価性を比較する。
     *
     * @param static $other 比較対象の値オブジェクト
     *
     * @return bool 比較結果
     */
    public function equals(self $other): bool
    {
        if (!($other instanceof static)) {
            return false;
        }

        return $this->value() === $other->value();
    }
}
