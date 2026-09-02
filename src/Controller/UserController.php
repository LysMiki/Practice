<?php

namespace App\Controller;

use App\Application\User\DTO\CreateUserRequest;
use App\Application\User\DTO\UpdateUserRequest;
use App\Application\User\UserService;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    #[Route('/v1/api/users', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $this->isGranted('ROLE_ROOT')
            ? $userRepository->findAll()
            : [$this->getUser()];

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'pass' => $user->getPass(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/v1/api/users', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateUserRequest $request,
        UserService $userService,
    ): JsonResponse {
        $user = $userService->create($request);

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/v1/api/users/{id}', methods: ['GET'])]
    public function show(
        int $id,
        UserRepository $userRepository,
    ): JsonResponse {
        $user = $userRepository->find($id);

        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass(),
        ]);
    }

    #[Route('/v1/api/users/{id}', methods: ['PUT'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateUserRequest $request,
        UserRepository $userRepository,
        UserService $userService,
    ): JsonResponse {
        $user = $userRepository->find($id);

        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $user = $userService->update($user, $request);

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass(),
        ]);
    }

    #[Route('/v1/api/users/{id}', methods: ['DELETE'])]
    public function delete(
        int $id,
        UserRepository $userRepository,
        UserService $userService,
    ): JsonResponse {
        $user = $userRepository->find($id);

        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);

        $userService->delete($user);

        return $this->json([
            'message' => 'User deleted',
        ]);
    }
}
