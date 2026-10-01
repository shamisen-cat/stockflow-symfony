<?php

declare(strict_types=1);

namespace App\Tests\Application\User\CreateUser;

use App\Application\User\CreateUser\CreateUserHandler;
use App\Application\User\CreateUser\CreateUserInput;
use App\Domain\Shared\Uuid\UuidGeneratorInterface;
use App\Domain\User\Email\Email;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Password\HashedPassword;
use App\Domain\User\Password\PlainPassword;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Tests\Support\MockClockTestTrait;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateUserHandlerTest extends TestCase
{
    use MockClockTestTrait;

    private CreateUserHandler $handler;
    private UserRepositoryInterface&MockObject $userRepository;
    private UuidGeneratorInterface&MockObject $uuidGenerator;
    private PlainPasswordHasherInterface&MockObject $plainPasswordHasher;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeClock();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->plainPasswordHasher = $this->createMock(PlainPasswordHasherInterface::class);

        $this->handler = new CreateUserHandler(
            userRepository: $this->userRepository,
            uuidGenerator: $this->uuidGenerator,
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
            email: $email->value(),
            password: $password->value(),
            createdAt: $createdAt,
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findActiveByEmail')
            ->with($email->value())
            ->willReturn(null);

        $id = Uuid::fromString('00000000-0000-7000-8000-000000000001');

        $this->uuidGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn($id);

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

        $createdId = $this->handler->handle($createUserInput);

        self::assertInstanceOf(User::class, $addedUser);
        self::assertSame($id, $createdId);
        self::assertSame($id, $addedUser->id);
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
            email: $email->value(),
            password: $password->value(),
            createdAt: $createdAt,
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findActiveByEmail')
            ->with($email->value())
            ->willReturn(UserTestFactory::create(email: $email));

        $this->uuidGenerator
            ->expects($this->never())
            ->method('generate');

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
