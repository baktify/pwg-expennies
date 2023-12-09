<?php

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\CategoryCreateRequestValidator;
use App\Services\CategoryService;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CategoryController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $categories = $this->categoryService->getAll();

        return $this->twig->render($response, 'categories/index.twig', compact('categories'));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CategoryCreateRequestValidator::class)->validate(
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

        $this->categoryService->delete($args['id']);

        return $response
            ->withHeader('Location', '/categories')
            ->withStatus(302);
    }
}