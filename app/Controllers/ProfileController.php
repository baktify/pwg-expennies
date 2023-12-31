<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\UserProfileData;
use App\Exceptions\ValidationException;
use App\RequestValidators\PasswordUpdateRequestValidator;
use App\RequestValidators\ProfileUpdateRequestValidator;
use App\Services\UserProfileService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ProfileController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly UserProfileService               $userProfileService,
        private readonly UserService                      $userService,
        private readonly EntityManagerServiceInterface    $entityManagerService,
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user')->getId();

        return $this->twig->render(
            $response,
            'profile/index.twig',
            ['profileData' => $this->userProfileService->get($userId)]
        );
    }

    public function update(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        $data = $this->requestValidatorFactory->make(ProfileUpdateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->userProfileService->update($user, new UserProfileData(
            $user->getEmail(),
            $data['name'],
            (bool)($data['twoFactor'] ?? false)
        ));

        return $response;
    }

    public function updatePassword(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(PasswordUpdateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $user = $request->getAttribute('user');
        if (!$user) {
            $response->getBody()->write('Unauthorized');

            return $response->withStatus(401);
        }

        if (!$this->userService->checkPasswordMatch($user, $data['currentPassword'])) {
            throw new ValidationException(['currentPassword' => ['Incorrect current password']]);
        }

        $this->userService->updatePassword($user, $data['newPassword']);
        $this->entityManagerService->sync();

        return $response;
    }
}