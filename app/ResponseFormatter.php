<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;

class ResponseFormatter
{
    public function asJson(
        ResponseInterface $response,
        mixed             $data,
        int               $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, $flags));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function asDataTable(ResponseInterface $response, array $data, int $draw, int $totalCategories): ResponseInterface
    {
        return $this->asJson(
            $response,
            [
                'data' => $data,
                'draw' => $draw,
                'recordsTotal' => $totalCategories,
                'recordsFiltered' => $totalCategories,
            ]
        );
    }
}