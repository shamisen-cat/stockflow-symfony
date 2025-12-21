<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

/**
 * 値オブジェクトに関する検証例外の基底クラス
 */
abstract class ValueObjectException extends \InvalidArgumentException
{
}
