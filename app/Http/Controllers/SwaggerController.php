<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API dokumentace aplikace',
    title: 'API Documentation',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'info@example.com'
    )
)]
#[OA\Server(
    url: 'http://localhost/',
    description: 'Test server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class SwaggerController extends Controller
{
    #[OA\Get(
        path: '/api/documentation',
        summary: 'Zobrazí dokumentaci API',
        tags: ['Documentation'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Úspěšná odpověď'
            )
        ]
    )]
    public function index(): View
    {
        return view('vendor.l5-swagger.index');
    }
}
