<?php

namespace App\Controllers;

use App\Auth;
use App\Entities\User;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Valitron\Validator;

class AuthController
{
    public function __construct(private readonly Twig $twig, private readonly EntityManager $em, private readonly Auth $auth)
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
        $data = $request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['email', 'password']);
        $v->rule('email', 'email');

        if (!$this->auth->attempt($data)) {
            throw new ValidationException(['password' => ['You have entered a wrong email or password']]);
        }

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['name', 'email', 'password']);
        $v->rule('required', 'confirmPassword')->label('Confirm Password');
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password');

        $v->rule(function ($field, $value, $params, $fields) {
            return !$this->em->getRepository(User::class)->count(['email' => $value]);
        }, 'email')->message('Entered email address already exists');

        if ($v->validate()) {
            echo "Yay! We're all good!";
        } else {
            throw new ValidationException($v->errors());
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]));

        $this->em->persist($user);
        $this->em->flush();

        return $response;
    }

    public function logOut(Request $request, Response $response): Response
    {
        $this->auth->logOut();

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}