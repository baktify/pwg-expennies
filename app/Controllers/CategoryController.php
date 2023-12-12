<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use App\RequestValidators\CategoryLoadRequestValidator;
use App\RequestValidators\CategoryCreateRequestValidator;
use App\RequestValidators\CategoryUpdateRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
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
        private readonly RequestService                   $requestService,
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CategoryCreateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $this->categoryService->create($data['name'], $request->getAttribute('user'));

        return $this->responseFormatter->asJson(
            $response,
            [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ]
        );
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

        return $this->responseFormatter->asJson(
            $response,
            [
                'id' => $category->getId(),
                'name' => $category->getName()
            ]
        );
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int)$args['id']);

        if (!$category) {
            return $response->withStatus(404);
        }

        $data = $this->requestValidatorFactory->make(CategoryUpdateRequestValidator::class)->validate(
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
        $params = $this->requestService->getDataTableQueryParams($request);

        $categories = $this->categoryService->getPaginatedCategories($params);
        $totalCategories = count($categories);

        $mapper = fn(Category $category) => [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'createdAt' => $category->getCreatedAt()->format('d/m/Y g:i A'),
            'updatedAt' => $category->getUpdatedAt()->format('d/m/Y g:i A'),
        ];

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($mapper, (array)$categories->getIterator()),
            $params->draw,
            $totalCategories,
        );
    }
}