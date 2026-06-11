<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Controller;

use App\Infrastructure\Security\Authenticator\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route(
        path: '/login',
        name: LoginFormAuthenticator::LOGIN_ROUTE,
        methods: ['GET', 'POST'],
    )]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_dev_users');
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        $error        = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route(
        path: '/logout',
        name: LoginFormAuthenticator::LOGOUT_ROUTE,
        methods: ['POST'],
    )]
    public function logout(): void
    {
        throw new \LogicException(
            'Logout is intercepted by main firewall in config/packages/security.yaml.',
        );
    }
}
