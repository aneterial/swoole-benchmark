<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

final readonly class SampleController
{
    public function index(ResponseInterface $response): PsrResponseInterface
    {
        return $response->json(['status' => 'ok']);
    }
}
