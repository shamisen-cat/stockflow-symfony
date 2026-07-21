<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Controller;

use App\Application\Shared\Transaction\TransactionManagerInterface;
use App\Application\User\CreateUser\CreateUserHandler;
use App\Application\User\CreateUser\CreateUserInput;
use App\Application\User\ListUsers\ListUsersHandler;
use App\Application\User\ListUsers\ListUsersInput;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\InvalidEmailException;
use App\Domain\User\Exception\InvalidPlainPasswordException;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\ValueObject\Email\EmailValidationResult;
use App\Domain\User\ValueObject\Password\PlainPasswordValidationResult;
use App\Infrastructure\Dev\Sidebar\DevSidebarFactory;
use App\Infrastructure\Dev\Sidebar\DevSidebarLinkId;
use App\Infrastructure\Security\Voter\DevToolsVoter;
use App\Infrastructure\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(DevToolsVoter::ACCESS_DEV_TOOLS)]
final class UserDevController extends AbstractController
{
    #[Route(
        path: '/dev/users',
        name: 'app_dev_users',
        methods: ['GET'],
    )]
    public function index(
        Request $request,
        ListUsersHandler $listUsersHandler,
        DevSidebarFactory $devSidebarFactory,
    ): Response {
        $email = trim($request->query->getString('email'));
        $sortKey = $request->query->getString('sort');
        $direction = $request->query->getString('direction');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 20);

        $listUsersInput = ListUsersInput::create(
            email: $email,
            sortKey: $sortKey,
            direction: $direction,
            page: $page,
            perPage: $perPage,
        );

        $result = $listUsersHandler->handle($listUsersInput);

        /** @var list<User> $users */
        $users = $result->pagination->getCurrentPageResults();

        $sidebar = $devSidebarFactory->create(DevSidebarLinkId::User);

        return $this->render('dev/user/index.html.twig', [
            'users' => $users,
            'pagination' => $result->pagination,
            'currentKey' => $result->currentSortKey,
            'currentDirection' => $result->currentSortDirection,
            'searchEmail' => $result->searchEmail,
            'sidebarLinks' => $sidebar->links,
            'sidebarSubLinks' => $sidebar->subLinks,
            'currentLink' => $sidebar->currentLink,
        ]);
    }

    #[Route(
        path: '/dev/users/export',
        name: 'app_dev_users_export',
        methods: ['GET'],
    )]
    public function export(
        Request $request,
        ListUsersHandler $listUsersHandler,
        ClockInterface $clock,
    ): Response {
        $email = trim($request->query->getString('email'));
        $sortKey = $request->query->getString('sort');
        $direction = $request->query->getString('direction');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 20);

        $listUsersInput = ListUsersInput::create(
            email: $email,
            sortKey: $sortKey,
            direction: $direction,
            page: $page,
            perPage: $perPage,
        );

        $result = $listUsersHandler->handle($listUsersInput);
        $now = $clock->now();

        /** @var list<User> $users */
        $users = $result->pagination->getCurrentPageResults();

        $response = new StreamedResponse(function () use ($users): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'id',
                'email',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id->toRfc4122(),
                    $user->email->value(),
                    $user->createdAt->format('Y-m-d H:i:s'),
                    $user->updatedAt->format('Y-m-d H:i:s'),
                    $user->deletedAt?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        });

        $response->headers->set(
            'Content-Type',
            'text/csv; charset=UTF-8',
        );

        $filename = sprintf(
            'users_%s_page-%d.csv',
            $now->format('Ymd_His'),
            $result->pagination->getCurrentPage(),
        );

        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', $filename),
        );

        return $response;
    }

    #[Route(
        path: '/dev/users/new',
        name: 'app_dev_users_new',
        methods: ['GET', 'POST'],
    )]
    public function new(
        Request $request,
        CreateUserHandler $createUserHandler,
        DevSidebarFactory $devSidebarFactory,
        TransactionManagerInterface $transactionManager,
        ClockInterface $clock,
        TranslatorInterface $translator,
    ): Response {
        $email = '';
        $error = null;

        if ($request->isMethod('POST')) {
            $token = $request->request->getString('_token');

            if (!$this->isCsrfTokenValid('dev_user_create', $token)) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $email = $request->request->getString('email');
            $password = $request->request->getString('password');

            try {
                $createUserInput = CreateUserInput::create(
                    email: $email,
                    password: $password,
                    createdAt: $clock->now(),
                );

                $transactionManager->transactional(
                    static fn (): Uuid => $createUserHandler->handle($createUserInput),
                );

                $flashMessage = $translator->trans('dev.user.flash.created', [
                    '%email%' => $createUserInput->email->value(),
                ]);

                $this->addFlash('success', $flashMessage);

                return $this->redirectToRoute('app_dev_users');
            } catch (InvalidEmailException $exception) {
                $error = match ($exception->result) {
                    EmailValidationResult::EMPTY => 'email.empty',
                    EmailValidationResult::TOO_LONG => 'email.too_long',
                    EmailValidationResult::INVALID_FORMAT => 'email.invalid_format',
                    EmailValidationResult::VALID => throw new \LogicException(
                        'InvalidEmailException must not have VALID result.',
                    ),
                };
            } catch (InvalidPlainPasswordException $exception) {
                $error = match ($exception->result) {
                    PlainPasswordValidationResult::EMPTY => 'password.empty',
                    PlainPasswordValidationResult::TOO_SHORT => 'password.too_short',
                    PlainPasswordValidationResult::TOO_LONG => 'password.too_long',
                    PlainPasswordValidationResult::VALID => throw new \LogicException(
                        'InvalidPlainPasswordException must not have VALID result.',
                    ),
                };
            } catch (UserAlreadyExistsException) {
                $error = 'user.already_exists';
            }

            $flashMessage = $translator->trans('dev.user.error.'.$error);

            $this->addFlash('error', $flashMessage);
        }

        $sidebar = $devSidebarFactory->create(DevSidebarLinkId::User);

        return $this->render('dev/user/new.html.twig', [
            'sidebarLinks' => $sidebar->links,
            'sidebarSubLinks' => $sidebar->subLinks,
            'currentLink' => $sidebar->currentLink,
            'email' => $email,
            'error' => $error,
        ]);
    }

    #[Route(
        path: '/dev/users/{id}/delete',
        name: 'app_dev_users_delete',
        methods: ['POST'],
    )]
    public function delete(
        string $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ClockInterface $clock,
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

        $user->softDelete($clock->now());

        $entityManager->persist($user);
        $entityManager->flush();

        $flashMessage = $translator->trans('dev.user.flash.deleted', [
            '%email%' => $user->email->value(),
        ]);

        $this->addFlash('success', $flashMessage);

        return $this->redirectToRoute('app_dev_users');
    }

    #[Route(
        path: '/dev/users/{id}/purge',
        name: 'app_dev_users_purge',
        methods: ['POST'],
    )]
    public function purge(
        string $id,
        Request $request,
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
