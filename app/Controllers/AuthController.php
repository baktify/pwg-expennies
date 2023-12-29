<?php

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\UserRegisterData;
use App\Enums\AuthAttemptStatus;
use App\Exceptions\ValidationException;
use App\Mail\SignupEmail;
use App\RequestValidators\TwoFactorLoginRequestValidator;
use App\RequestValidators\UserLogInRequestValidator;
use App\RequestValidators\UserRegisterRequestValidator;
use App\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly AuthInterface                    $auth,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter                $responseFormatter,
        private readonly SignupEmail                      $signupEmail,
    )
    {
    }

    public function loginView(Response $response): Response
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function registerView(Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.twig');
    }

    public function logIn(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserLogInRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $status = $this->auth->attempt($data);

        if ($status === AuthAttemptStatus::FAILED) {
            throw new ValidationException(['password' => ['You have entered a wrong email or password']]);
        }

        if ($status === AuthAttemptStatus::INTERNAL_SERVER_ERROR) {
            return $this->responseFormatter->asJson($response->withStatus(500), ['message' => ['error' => 'Internal server error']]);
        }

        if ($status === AuthAttemptStatus::TWO_FACTOR_AUTH) {
            return $this->responseFormatter->asJson($response, ['two_factor' => true]);
        }

        return $this->responseFormatter->asJson($response);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserRegisterRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $user = $this->auth->register(
            new UserRegisterData($data['name'], $data['email'], $data['password'])
        );

        $this->signupEmail->send($user);

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function logOut(Response $response): Response
    {
        $this->auth->logOut();

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    public function loginTwoFactor(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TwoFactorLoginRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $status = $this->auth->attempt2FA($data['code']);

        if ($status === AuthAttemptStatus::FAILED) {
            return $this->responseFormatter->asJson($response->withStatus(500), ['code' => ['Something went wrong']]);
        }

        return $response;
    }
}