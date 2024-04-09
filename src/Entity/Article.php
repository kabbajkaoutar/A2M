<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;

//Appliquer directives de cache à une ressource Article, en spécifiant une durée de cache (maxAge), une durée de cache partagé (sharedMaxAge), et en marquant le cache comme public.
#[ApiCache(maxAge: 3600, sharedMaxAge: 3600, public: true)]
#[ApiResource(operations: [
    new Get(),
    // pour delete et patch seulement utilisateur connecte qui a le token aura le droit de les acceder pour ceci on utilise le bearerAuth creer dans OpenApiFactory
    //Only Admin can delete an article
    new Delete(
        openapiContext: [
            'security' => [['bearerAuth' => []]]
        ],
        security: "is_granted('ROLE_ADMIN')"

    ),
    new Patch(
        openapiContext: [
            'security' => [['bearerAuth' => []]]
        ],
         security: "is_granted('ROLE_ADMIN')"

    ),
    // desactiver la pagination par page
    new GetCollection(
        paginationEnabled: false
    )
])]

//#[ApiFilter(SearchFilter::class, properties: ['title'])]

#[ORM\Entity]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $context = null;

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function __construct(?array $context = null)
    {
        $this->context = $context;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }


}
