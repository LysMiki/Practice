<?php

namespace App\Application\User;

use App\Application\User\DTO\CreateUserRequest;
use App\Application\User\DTO\UpdateUserRequest;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRepository;

final class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function create(CreateUserRequest $request): User
    {
        $existingUser = $this->userRepository->findOneByLoginAndPass(
            $request->login,
            $request->pass,
        );

        if ($existingUser !== null) {
            throw new UserAlreadyExistsException();
        }

        $user = new User();

        $user->setLogin($request->login);
        $user->setPhone($request->phone);
        $user->setPass($request->pass);

        $this->userRepository->save($user);

        return $user;
    }

    public function update(
        User $user,
        UpdateUserRequest $request,
    ): User {
        $existingUser = $this->userRepository->findOneByLoginAndPass(
            $request->login,
            $request->pass,
        );

        if (
            $existingUser !== null
            && $existingUser->getId() !== $user->getId()
        ) {
            throw new UserAlreadyExistsException();
        }

        $user->setLogin($request->login);
        $user->setPhone($request->phone);
        $user->setPass($request->pass);

        $this->userRepository->save($user);

        return $user;
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user);
    }
}
