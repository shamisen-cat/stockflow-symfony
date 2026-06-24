<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\PlainPassword;
use App\Infrastructure\Security\Voter\DevToolsVoter;
use App\Infrastructure\Shared\Pagination\PaginationFactory;
use App\Infrastructure\Shared\Pagination\SortDirection;
use App\Infrastructure\Shared\Pagination\SortResolver;
use App\Infrastructure\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/dev')]
#[IsGranted(DevToolsVoter::ACCESS_DEV_TOOLS)]
final class UserDevController extends AbstractController
{
    #[Route(
        path: '/users',
        name: 'app_dev_users',
        methods: ['GET'],
    )]
    public function index(
        Request $request,
        UserRepository $userRepository,
        SortResolver $sortResolver,
        PaginationFactory $paginationFactory,
    ): Response {
        $defaultKey = 'updated_at';
        $sortMap = [
            'id' => 'u.id',
            'email' => 'u.email.value',
            'created_at' => 'u.createdAt',
            $defaultKey => 'u.updatedAt',
        ];

        $sort = $sortResolver->resolve(
            $request,
            $sortMap,
            $defaultKey,
            SortDirection::Desc,
        );

        $queryBuilder = $userRepository->createListQueryBuilder();
        $userRepository->applyListSort($queryBuilder, $sort);

        $pager = $paginationFactory->create(
            queryBuilder: $queryBuilder,
            page: $request->query->getInt('page', 1),
            maxPerPage: $request->query->getInt('per_page', 20),
        );

        return $this->render('dev/user/index.html.twig', [
            'users' => $pager->getCurrentPageResults(),
            'pagination' => $pager,
            'currentKey' => $sort->key,
            'currentDirection' => $sort->direction,
        ]);
    }

    #[Route(
        path: '/users/create',
        name: 'app_dev_users_create',
        methods: ['POST'],
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        PlainPasswordHasherInterface $plainPasswordHasher,
        TranslatorInterface $translator,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_create', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $email = sprintf('%s@example.com', bin2hex(random_bytes(16)));
        $password = 'stockflow-dev';

        $plainPassword = PlainPassword::of($password);
        $hashedPassword = $plainPasswordHasher->hash($plainPassword);

        $user = User::create(
            id: Uuid::v7(),
            email: Email::of($email),
            password: $hashedPassword,
            createdAt: new \DateTimeImmutable(),
        );

        $entityManager->persist($user);
        $entityManager->flush();

        $flashMessage = $translator->trans('dev.user.flash.created', [
            '%email%' => $email,
        ]);

        $this->addFlash('success', $flashMessage);

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
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_delete', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $userRepository->find(Uuid::fromString($id));

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $user->softDelete(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        $flashMessage = $translator->trans('dev.user.flash.deleted', [
            '%email%' => $user->email->value(),
        ]);

        $this->addFlash('success', $flashMessage);

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
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $token = $request->request->getString('_token');

        if (!$this->isCsrfTokenValid('dev_user_purge', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $userRepository->find(Uuid::fromString($id));

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $email = $user->email->value();

        $entityManager->remove($user);
        $entityManager->flush();

        $flashMessage = $translator->trans('dev.user.flash.purged', [
            '%email%' => $email,
        ]);

        $this->addFlash('warning', $flashMessage);

        return $this->redirectToRoute('app_dev_users');
    }
}
