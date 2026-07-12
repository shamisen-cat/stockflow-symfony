<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Authenticator;

use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const string LOGIN_ROUTE = 'app_login';
    public const string LOGOUT_ROUTE = '_logout_main';
    public const string CSRF_TOKEN_ID = 'authenticate';

    private const string DEFAULT_TARGET_PATH = '/dev/users';

    /**
     * @param UserProviderInterface<User> $userProvider
     */
    public function __construct(
        private readonly UserProviderInterface $userProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @see AuthenticatorInterface
     */
    #[\Override]
    public function authenticate(Request $request): Passport
    {
        $username = $request->request->getString('_username');
        $password = $request->request->getString('_password');
        $csrfToken = $request->request->getString('_csrf_token');

        $userBadge = new UserBadge(
            userIdentifier: $username,
            userLoader: function (string $identifier): User {
                return $this->userProvider->loadUserByIdentifier($identifier);
            },
        );
        $credentials = new PasswordCredentials($password);
        $badges = [new CsrfTokenBadge(self::CSRF_TOKEN_ID, $csrfToken)];

        return new Passport($userBadge, $credentials, $badges);
    }

    /**
     * @see AuthenticatorInterface
     */
    #[\Override]
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): Response {
        if ($request->hasSession()) {
            $session = $request->getSession();

            $session->remove(SecurityRequestAttributes::LAST_USERNAME);
            $session->remove(SecurityRequestAttributes::AUTHENTICATION_ERROR);

            $targetPath = $this->getTargetPath($session, $firewallName);

            if ($targetPath !== null) {
                return new RedirectResponse($targetPath);
            }

            $this->removeTargetPath($session, $firewallName);
        }

        return new RedirectResponse(self::DEFAULT_TARGET_PATH);
    }

    /**
     * @see AbstractLoginFormAuthenticator
     */
    #[\Override]
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): Response {
        if ($request->hasSession()) {
            $session = $request->getSession();
            $username = $request->request->getString('_username');

            $session->set(SecurityRequestAttributes::LAST_USERNAME, $username);
            $session->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->getLoginUrl($request));
    }

    /**
     * @see AbstractLoginFormAuthenticator
     */
    #[\Override]
    protected function getLoginUrl(Request $request): string
    {
        $loginUrl = $this->urlGenerator->generate(
            name: self::LOGIN_ROUTE,
            referenceType: UrlGeneratorInterface::ABSOLUTE_PATH,
        );

        return $loginUrl;
    }
}
