<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/v1/api');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authorization = $request->headers->get('Authorization');

        if ($authorization === null) {
            throw new AuthenticationException('Bearer token is missing.');
        }

        if (!str_starts_with($authorization, 'Bearer ')) {
            throw new AuthenticationException('Invalid authorization header.');
        }

        $token = trim(substr($authorization, 7));

        if ($token === '') {
            throw new AuthenticationException('Bearer token is missing.');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function (string $token) {
                $user = $this->userRepository->findOneBy([
                    'token' => $token,
                ]);

                if ($user === null) {
                    throw new AuthenticationException('Invalid bearer token.');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {
        return new JsonResponse([
            'error' => 'Authentication failed',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
