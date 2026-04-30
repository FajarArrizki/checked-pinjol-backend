<?php

declare(strict_types=1);

namespace App\Modules\Health\Controllers;

use App\Core\Config\ConfigRepository;
use App\Core\Http\Request;
use App\Core\Http\Response;

final class HealthController
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function index(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'message' => 'Checked Pinjol backend is running.',
            'data' => [
                'app' => $this->config->get('app.name'),
                'env' => $this->config->get('app.env'),
                'time' => date(DATE_ATOM),
            ],
        ]);
    }
}
