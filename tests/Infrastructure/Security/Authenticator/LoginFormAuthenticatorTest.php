<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Security\Authenticator;

use App\Domain\User\Entity\User;
use App\Infrastructure\Security\Authenticator\LoginFormAuthenticator;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

final class LoginFormAuthenticatorTest extends TestCase
{
    private const string FIREWALL_NAME = 'main';

    #[Test]
    public function authenticateReturnsPassportWithCredentialsFromRequest(): void
    {
        $username  = 'test@example.com';
        $password  = 'test-password';
        $csrfToken = 'test-csrf-token';

        $authenticator = $this->createAuthenticator();
        $request       = $this->createLoginRequest($username, $password, $csrfToken);

        $passport = $authenticator->authenticate($request);

        $userBadge = $passport->getBadge(UserBadge::class);

        self::assertInstanceOf(UserBadge::class, $userBadge);
        self::assertSame($username, $userBadge->getUserIdentifier());

        $passwordCredentials = $passport->getBadge(PasswordCredentials::class);

        self::assertInstanceOf(PasswordCredentials::class, $passwordCredentials);
        self::assertSame($password, $passwordCredentials->getPassword());

        $csrfTokenBadge = $passport->getBadge(CsrfTokenBadge::class);

        self::assertInstanceOf(CsrfTokenBadge::class, $csrfTokenBadge);
        self::assertSame(LoginFormAuthenticator::CSRF_TOKEN_ID, $csrfTokenBadge->getCsrfTokenId());
        self::assertSame($csrfToken, $csrfTokenBadge->getCsrfToken());
    }

    #[Test]
    public function authenticateLoadsUserFromProvider(): void
    {
        $user    = UserTestFactory::create();
        $request = $this->createLoginRequest(username: $user->email->value());

        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider
            ->expects(self::once())
            ->method('loadUserByIdentifier')
            ->with($user->email->value())
            ->willReturn($user);

        $authenticator = $this->createAuthenticator(userProvider: $userProvider);

        $passport = $authenticator->authenticate($request);

        self::assertSame($user, $passport->getUser());
    }

    #[Test]
    public function onAuthenticationSuccessRedirectsToTargetPathWhenSessionHasTargetPath(): void
    {
        $firewallName = self::FIREWALL_NAME;

        $targetPathSessionKey = '_security.'.$firewallName.'.target_path';
        $targetPath           = '/target/page';
        $exception            = new AuthenticationException('Invalid credentials.');

        $session = new Session(new MockArraySessionStorage());
        $session->set($targetPathSessionKey, $targetPath);
        $session->set(SecurityRequestAttributes::LAST_USERNAME, 'lastUsername');
        $session->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        $request = Request::create('/login');
        $request->setSession($session);
        $token = self::createStub(TokenInterface::class);

        $authenticator = $this->createAuthenticator();

        $response = $authenticator->onAuthenticationSuccess($request, $token, $firewallName);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($targetPath, $response->getTargetUrl());
        self::assertFalse($session->has(SecurityRequestAttributes::LAST_USERNAME));
        self::assertFalse($session->has(SecurityRequestAttributes::AUTHENTICATION_ERROR));
    }

    #[Test]
    public function onAuthenticationSuccessRedirectsToDefaultTargetPath(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $request = Request::create('/login');
        $request->setSession($session);
        $token = self::createStub(TokenInterface::class);

        $authenticator = $this->createAuthenticator();

        $response = $authenticator->onAuthenticationSuccess($request, $token, self::FIREWALL_NAME);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/dev/users', $response->getTargetUrl());
    }

    #[Test]
    public function onAuthenticationSuccessRedirectsToDefaultTargetPathWhenRequestHasNoSession(): void
    {
        $request = Request::create('/login');
        $token   = self::createStub(TokenInterface::class);

        $authenticator = $this->createAuthenticator();

        $response = $authenticator->onAuthenticationSuccess($request, $token, self::FIREWALL_NAME);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/dev/users', $response->getTargetUrl());
    }

    #[Test]
    public function onAuthenticationFailureStoresSessionDataAndRedirectsToLogin(): void
    {
        $username  = 'lastUsername';
        $exception = new AuthenticationException('Invalid credentials.');

        $session = new Session(new MockArraySessionStorage());

        $request = $this->createLoginRequest(username: $username);
        $request->setSession($session);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with(
                LoginFormAuthenticator::LOGIN_ROUTE,
                [],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/login');

        $authenticator = $this->createAuthenticator(urlGenerator: $urlGenerator);

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
        self::assertSame($username, $session->get(SecurityRequestAttributes::LAST_USERNAME));
        self::assertSame($exception, $session->get(SecurityRequestAttributes::AUTHENTICATION_ERROR));
    }

    /**
     * @param UserProviderInterface<User>|null $userProvider
     */
    private function createAuthenticator(
        ?UrlGeneratorInterface $urlGenerator = null,
        ?UserProviderInterface $userProvider = null,
    ): LoginFormAuthenticator {
        return new LoginFormAuthenticator(
            $urlGenerator ?? self::createStub(UrlGeneratorInterface::class),
            $userProvider ?? self::createStub(UserProviderInterface::class),
        );
    }

    private function createLoginRequest(
        string $username = 'test@example.com',
        string $password = 'test-password',
        string $csrfToken = 'test-csrf-token',
    ): Request {
        return Request::create('/login', 'POST', [
            '_username'   => $username,
            '_password'   => $password,
            '_csrf_token' => $csrfToken,
        ]);
    }
}
