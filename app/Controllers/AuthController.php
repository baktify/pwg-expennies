<?php

namespace App\Controllers;

use App\Entities\User;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Valitron\Validator;

class AuthController
{
    public function __construct(private readonly Twig $twig, private readonly EntityManager $em)
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

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            throw new ValidationException(['password' => ['You have entered a wrong email or password']]);
        }

        session_regenerate_id();

        $_SESSION['user'] = $user->getId();

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
}