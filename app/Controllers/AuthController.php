<?php

namespace App\Controllers;

use App\Auth;
use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\UserRegisterData;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\RequestValidators\UserLogInRequestValidator;
use App\RequestValidators\UserRegisterRequestValidator;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Valitron\Validator;

class AuthController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly AuthInterface                    $auth,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
    )
    {
    }

    public function loginView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function registerView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.twig');
    }

    public function logIn(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserLogInRequestValidator::class)->validate($request->getParsedBody());

        if (!$this->auth->attempt($data)) {
            throw new ValidationException(['password' => ['You have entered a wrong email or password']]);
        }

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserRegisterRequestValidator::class)->validate($request->getParsedBody());

        $this->auth->register(new UserRegisterData(
            $data['name'], $data['email'], $data['password']
        ));

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function logOut(Request $request, Response $response): Response
    {
        $this->auth->logOut();

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}