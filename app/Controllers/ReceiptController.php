<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReceiptController
{
    public function store(Request $request, Response $response, array $args): Response
    {
        dd($request->getParsedBody(), $request->getUploadedFiles(), $_FILES, $args);

        return $response;
    }
}