<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    #[Route('/v1/api/users', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        if ($this->isGranted('ROLE_ROOT')) {
            $users = $userRepository->findAll();
        } else {
            $users = [$this->getUser()];
        }

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
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'Invalid JSON'
            ], 400);
        }

        $user = new User();

        $user->setLogin($data['login'] ?? '');
        $user->setPhone($data['phone'] ?? '');
        $user->setPass($data['pass'] ?? '');

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $validationErrors = [];

            foreach ($errors as $error) {
                $validationErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json([
                'error' => 'Validation failed',
                'violations' => $validationErrors,
            ], 422);
        }
        $entityManager->persist($user);

        try {
            $entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
            return $this->json([
                'error' => 'User with this login and pass already exists'
            ], 409);
        }

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass(),
        ], 201);
    }

    #[Route('/v1/api/users/{id}', methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if ($user === null) {
            return $this->json([
                'error' => 'User not found'
            ], 404);
        }

        if (
            !$this->isGranted('ROLE_ROOT')
            && $this->getUser()->getId() !== $user->getId()
        ) {
            return $this->json([
                'error' => 'Access denied'
            ], 403);
        }

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
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $userRepository->find($id);

        if ($user === null) {
            return $this->json([
                'error' => 'User not found'
            ], 404);
        }

        if (
            !$this->isGranted('ROLE_ROOT')
            && $this->getUser()->getId() !== $user->getId()
        ) {
            return $this->json([
                'error' => 'Access denied'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'Invalid JSON'
            ], 400);
        }

        $user->setLogin($data['login'] ?? '');
        $user->setPhone($data['phone'] ?? '');
        $user->setPass($data['pass'] ?? '');

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $validationErrors = [];

            foreach ($errors as $error) {
                $validationErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json([
                'error' => 'Validation failed',
                'violations' => $validationErrors,
            ], 422);
        }

        try {
            $entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
            return $this->json([
                'error' => 'User with this login and pass already exists'
            ], 409);
        }

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
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$this->isGranted('ROLE_ROOT')) {
            return $this->json([
                'error' => 'Access denied'
            ], 403);
        }

        $user = $userRepository->find($id);

        if ($user === null) {
            return $this->json([
                'error' => 'User not found'
            ], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User deleted'
        ]);
    }
}
