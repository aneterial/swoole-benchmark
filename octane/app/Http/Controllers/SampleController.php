<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final readonly class SampleController
{
    /**
     * @return array{status: string}
     */
    public function index(): array
    {
        return ['status' => 'ok'];
    }
}
