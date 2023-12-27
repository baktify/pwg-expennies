<?php

namespace App\Controllers;

use App\Entities\User;
use App\Mail\SignupEmail;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class VerificationController
{
    public function __construct(
        private readonly Twig        $twig,
        private readonly UserService $userService,
        private readonly SignupEmail $signupEmail,
    )
    {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'auth/verify.twig');
    }

    public function verify(Request $request, Response $response, array $args): Response
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $userId = $args['userId'];
        $emailHash = $args['emailHash'];

        if (($user->getId() !== (int)$userId) || !hash_equals($emailHash, sha1($user->getEmail()))) {
            throw new \RuntimeException('Failed to verify account');
        }

        $this->userService->verifyUser($user);

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function resendVerification(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        $this->signupEmail->send($user);

        $response->getBody()->write('Verification link was sent.');
        return $response;
    }
}