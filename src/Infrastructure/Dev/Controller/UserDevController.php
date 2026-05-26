<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email\Email;
use App\Infrastructure\Security\Voter\DevToolsVoter;
use App\Infrastructure\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route(path: '/dev')]
#[IsGranted(DevToolsVoter::ACCESS_DEV_TOOLS)]
final class UserDevController extends AbstractController
{
    private const string DEV_USER_EMAIL = 'dev@example.com';

    #[Route(
        path: '/users',
        name: 'app_dev_users',
        methods: ['GET'],
    )]
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findBy(
            criteria: [],
            orderBy: ['id' => 'ASC'],
        );

        return $this->render('dev/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route(
        path: '/users/create',
        name: 'app_dev_users_create',
        methods: ['POST'],
    )]
    public function create(
        Request $request,
        UserRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_create', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $email = $repository->findOneBy(['email.value' => self::DEV_USER_EMAIL]) === null
            ? self::DEV_USER_EMAIL
            : sprintf('dev-%s@example.com', bin2hex(random_bytes(16)));

        $user = new User(
            id: Uuid::v7(),
            email: Email::of($email),
        );

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_dev_users');
    }

    #[Route(
        path: '/users/{id}/delete',
        name: 'app_dev_users_delete',
        methods: ['POST'],
    )]
    public function delete(
        Request $request,
        string $id,
        UserRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_delete', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $repository->find(Uuid::fromString($id));

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $user->softDelete(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_dev_users');
    }

    #[Route(
        path: '/users/{id}/purge',
        name: 'app_dev_users_purge',
        methods: ['POST'],
    )]
    public function purge(
        Request $request,
        string $id,
        UserRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_purge', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $repository->find(Uuid::fromString($id));

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_dev_users');
    }
}
