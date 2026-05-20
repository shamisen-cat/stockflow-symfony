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
        $voter    = new DevToolsVoter('dev');
        $token    = new NullToken();
        $expected = VoterInterface::ACCESS_GRANTED;

        $result = $voter->vote(
            token: $token,
            subject: null,
            attributes: [DevToolsVoter::ACCESS_DEV_TOOLS],
        );

        self::assertSame($expected, $result);
    }

    #[Test]
    public function deniesAccessOutsideDevEnvironment(): void
    {
        $voter    = new DevToolsVoter('prod');
        $token    = new NullToken();
        $expected = VoterInterface::ACCESS_DENIED;

        $result = $voter->vote(
            token: $token,
            subject: null,
            attributes: [DevToolsVoter::ACCESS_DEV_TOOLS],
        );

        self::assertSame($expected, $result);
    }

    #[Test]
    public function abstainsForUnsupportedAttribute(): void
    {
        $voter    = new DevToolsVoter('dev');
        $token    = new NullToken();
        $expected = VoterInterface::ACCESS_ABSTAIN;

        $result = $voter->vote(
            token: $token,
            subject: null,
            attributes: ['B'.substr(DevToolsVoter::ACCESS_DEV_TOOLS, 1)],
        );

        self::assertSame($expected, $result);
    }
}
