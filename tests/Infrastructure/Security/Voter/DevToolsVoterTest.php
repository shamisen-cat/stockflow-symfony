<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Security\Voter;

use App\Infrastructure\Security\Voter\DevToolsVoter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class DevToolsVoterTest extends TestCase
{
    #[Test]
    public function grantsAccessInDevEnvironment(): void
    {
        $voter = new DevToolsVoter('dev');

        $result = $voter->vote(
            token: new NullToken(),
            subject: null,
            attributes: [DevToolsVoter::ACCESS_DEV_TOOLS],
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    #[Test]
    public function deniesAccessOutsideDevEnvironment(): void
    {
        $voter = new DevToolsVoter('prod');

        $result = $voter->vote(
            token: new NullToken(),
            subject: null,
            attributes: [DevToolsVoter::ACCESS_DEV_TOOLS],
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    #[Test]
    public function abstainsForUnsupportedAttribute(): void
    {
        $voter = new DevToolsVoter('dev');

        $result = $voter->vote(
            token: new NullToken(),
            subject: null,
            attributes: ['BCCESS_DEV_TOOLS'],
        );

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
