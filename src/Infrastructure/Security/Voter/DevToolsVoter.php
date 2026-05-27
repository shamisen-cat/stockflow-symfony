<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class DevToolsVoter extends Voter
{
    public const string ACCESS_DEV_TOOLS = 'ACCESS_DEV_TOOLS';

    public function __construct(
        #[Autowire(param: 'kernel.environment')]
        private readonly string $kernelEnvironment,
    ) {
    }

    /**
     * @see Voter
     */
    #[\Override]
    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return $attribute === self::ACCESS_DEV_TOOLS;
    }

    // TODO: 認可実装時に dev 環境チェックを差し替え
    /**
     * @see Voter
     */
    #[\Override]
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        return $this->kernelEnvironment === 'dev';
    }
}
