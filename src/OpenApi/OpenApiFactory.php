<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {

    }

    public function __invoke(array $context = []): \ApiPlatform\OpenApi\OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $schema = $openApi->getComponents()->getSecuritySchemes();
        $schema['bearerAuth'] = new \ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
            'description' => 'A JWT token is required to access this API. JWT token should be passed in `Authorization` header with value `Bearer <JWT_TOKEN>` format.',
            'in' => 'header',
            'name' => 'Authorization'
        ]);


        ## This will display a customize end point for authentification which include login and logout
        /*
         *
           $schemas = $openApi->getComponents()->getSchemas();
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => ['username' => ['type' => 'string', 'example' => 'admin'], 'password' => ['type' => 'string', 'example' => 'password']],
        ]);
        $pathItem = new PathItem(
            post: new Operation(
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                ),
                tags: ['Authentification'],
                responses: [
                    '200' => [
                        'description' => 'Utilisateur connecté',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User-read.User'
                                ]
                            ]
                        ]
                    ]
                ]
            ),
        );
        $openApi->getPaths()->addPath('/api/login_check', $pathItem);*/


        $schemas = $openApi->getComponents()->getSchemas();
        $pathItem = new PathItem(
            get: new Operation(
                description: 'Get articles from diffrent Ressources',
                tags: ['Chargement des articles'],
                responses: [
                    '200' => [
                        'description' => 'Get articles from diffrent Ressources',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Article'
                                ]
                            ]
                        ]
                    ]
                ]
            ),
        );
        $openApi->getPaths()->addPath('/api/chargeArticles', $pathItem);


        $pathItem = new PathItem(
            post: new Operation(
                operationId: 'api_logout',
                tags: ['Login Check'],
                summary: 'Deconnexion',
                responses: [
                    '204' => [
                        'description' => 'Utilisateur déconnecté avec succes et Bearer token supprimé',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User'
                                ]
                            ]
                        ]
                    ]
                ]

            ),
        );
        $openApi->getPaths()->addPath('/api/logout', $pathItem);
        return $openApi;
    }
}

?>