<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Category;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CategoryController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService                  $categoryService,
        private readonly ResponseFormatter                $responseFormatter,
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $this->categoryService->create($data['name'], $request->getAttribute('user'));

        return $response
            ->withHeader('Location', '/categories')
            ->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        // TODO: Validate the id

        $this->categoryService->delete((int)$args['id']);

        return $response;
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int)$args['id']);

        if (!$category) {
            return $response->withStatus(404);
        }

        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int)$args['id']);

        if (!$category) {
            return $response->withStatus(404);
        }

        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $this->categoryService->update($category, $data['name']);

        return $this->responseFormatter->asJson($response, [
            'id' => $category->getId(),
            'name' => $category->getName(),
        ]);
    }

    public function load(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        $categories = $this->categoryService->getPaginatedCategories((int)$params['start'], (int)$params['length']);
        $totalCategories = count($categories);

        $mapper = function (Category $category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('d/m/Y g:i A'),
                'updatedAt' => $category->getUpdatedAt()->format('d/m/Y g:i A'),
            ];
        };

        return $this->responseFormatter->asJson($response, [
            'data' => array_map($mapper, (array)$categories->getIterator()),
            'draw' => (int)$params['draw'],
            'recordsTotal' => $totalCategories,
            'recordsFiltered' => $totalCategories,
        ]);
    }
}