<?php

declare(strict_types=1);

namespace App\Tests\Application\User\CreateUser;

use App\Application\User\CreateUser\CreateUserHandler;
use App\Application\User\CreateUser\CreateUserInput;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Domain\User\ValueObject\Password\PlainPassword;
use App\Tests\Support\MockClockTestTrait;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    use MockClockTestTrait;

    private UserRepositoryInterface&MockObject $userRepository;
    private PlainPasswordHasherInterface&MockObject $plainPasswordHasher;
    private CreateUserHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeClock();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->plainPasswordHasher = $this->createMock(PlainPasswordHasherInterface::class);

        $this->handler = new CreateUserHandler(
            userRepository: $this->userRepository,
            plainPasswordHasher: $this->plainPasswordHasher,
        );
    }

    #[Test]
    public function handleCreatesAndPersistsUser(): void
    {
        $email = Email::of('test@example.com');
        $password = PlainPassword::of('test-password');
        $createdAt = $this->now();

        $createUserInput = new CreateUserInput(
            email: $email,
            password: $password,
            createdAt: $createdAt,
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findActiveByEmail')
            ->with($email->value())
            ->willReturn(null);

        $hashedPassword = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash');

        $this->plainPasswordHasher
            ->expects($this->once())
            ->method('hash')
            ->with(self::callback(
                static fn (PlainPassword $plainPassword): bool => $plainPassword->equals($password),
            ))
            ->willReturn($hashedPassword);

        $addedUser = null;

        $this->userRepository
            ->expects($this->once())
            ->method('add')
            ->willReturnCallback(
                static function (User $user) use (&$addedUser): void {
                    $addedUser = $user;
                },
            );

        $userId = $this->handler->handle($createUserInput);

        self::assertInstanceOf(User::class, $addedUser);
        self::assertSame($addedUser->id, $userId);
        self::assertTrue($addedUser->email->equals($email));
        self::assertTrue($addedUser->password->equals($hashedPassword));
        self::assertSame($createdAt, $addedUser->createdAt);
    }

    #[Test]
    public function handleThrowsWhenEmailAlreadyExists(): void
    {
        $email = Email::of('test@example.com');
        $password = PlainPassword::of('test-password');
        $createdAt = $this->now();

        $createUserInput = new CreateUserInput(
            email: $email,
            password: $password,
            createdAt: $createdAt,
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findActiveByEmail')
            ->with($email->value())
            ->willReturn(UserTestFactory::create(email: $email));

        $this->plainPasswordHasher
            ->expects($this->never())
            ->method('hash');

        $this->userRepository
            ->expects($this->never())
            ->method('add');

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessageIs('User already exists.');

        $this->handler->handle($createUserInput);
    }
}
