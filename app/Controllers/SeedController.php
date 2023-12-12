<?php

namespace App\Controllers;

use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\UserRegisterData;
use App\Entities\Category;
use App\Entities\User;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SeedController
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly UserProviderServiceInterface $userProviderService
    )
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
//        $this->seedAdmin();
        $this->seedCategories();

        $response->getBody()->write('Seeding finished successfully.');
        return $response;
    }

    private function seedCategories()
    {
        $faker = Factory::create();

        for ($i = 0; $i < 85; $i++) {
            $user = $this->em->getRepository(User::class)->findOneBy([]);

            $category  = new Category();
            $category->setName(ucfirst($faker->word()));
            $category->setUser($user);
            $category->setCreatedAt($faker->dateTimeInInterval('-18 months', '-4 years'));
            $category->setUpdatedAt($faker->dateTimeInInterval('-18 months', '+18 months'));

            $this->em->persist($category);
        }
        $this->em->flush();
    }

    private function seedAdmin()
    {
        $this->userProviderService->createUser(
            new UserRegisterData('Bakyt Zhan', 'bako@mail.ru', '123')
        );
    }
}