<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Exceptions\InvalidLinkException;
use App\Mail\PasswordResetEmail;
use App\RequestValidators\ForgotPasswordRequestValidator;
use App\RequestValidators\PasswordResetRequestValidator;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PasswordResetController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly PasswordResetService             $passwordResetService,
        private readonly EntityManagerServiceInterface    $entityManagerService,
        private readonly PasswordResetEmail               $passwordResetEmail,
        private readonly UserService                      $userService,
    )
    {
    }

    public function showForgotPasswordForm(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/forgot_password.twig');
    }

    public function handleForgotPasswordRequest(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(ForgotPasswordRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $user = $this->userService->getByCredentials(['email' => $data['email']]);

        if ($user) {
            $email = $user->getEmail();

            $this->passwordResetService->deactivateAllActivePasswordResets($email);

            // Create PasswordReset instance
            $passwordReset = $this->passwordResetService->create($email);
            $this->entityManagerService->sync($passwordReset);

            // Send an email
            $this->passwordResetEmail->send($passwordReset);
        }

        return $response;
    }

    public function showResetPasswordForm(Response $response, array $args): Response
    {
        $token = $args['token'];

        // Verify if passwordReset token is active
        if (!$this->passwordResetService->verify($token)) {
            $response->getBody()->write('Token has expired');
            return $response;
        }

        return $this->twig->render($response, 'auth/reset_password.twig', compact('token'));
    }

    public function handleResetPasswordRequest(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];

        // Verify if passwordReset token is active
        if (!$this->passwordResetService->verify($token)) {
            $response->getBody()->write('Token has expired');
            return $response;
        }

        $data = $this->requestValidatorFactory->make(PasswordResetRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $passwordReset = $this->passwordResetService->getByToken($args['token']);
        if (!$passwordReset) {
            throw new InvalidLinkException();
        }

        $user = $this->userService->getByCredentials(['email' => $passwordReset->getEmail()]);
        if (!$user) {
            throw new InvalidLinkException();
        }

        $this->userService->updatePassword($user, $data['password']);
        $this->entityManagerService->sync($user);

        $this->passwordResetService->deactivateAllActivePasswordResets($user->getEmail());

        return $response;
    }
}