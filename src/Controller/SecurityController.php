<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ArticleFetcher;
use App\Entity\Article;
use DateTimeImmutable;
class SecurityController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login()
    {
      $user = $this->getUser();
      return $this->json(['user' => $user->getUsername(),
          'roles' => $user->getRoles()]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): Response
    {
        $response = new Response();
        $response->headers->clearCookie('Bearer');

        // Pour supprimer le token du header Authorization (si nécessaire)
        $response->headers->remove('Authorization');

        return $response;
    }

    #[Route('/api/chargeArticles', name: 'api_chargeArticles', methods: ['GET'])]
    public function chargeArticles(ArticleFetcher $articleFetcher, EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch articles from the RSS feed and JSON API
        $articles = array_merge(
            $articleFetcher->fetchRssArticles("https://www.lemonde.fr/rss/une.xml"),
            $articleFetcher->fetchJsonArticles("https://api.spaceflightnewsapi.net/v3/articles")
        );

        // Convert the array of articles into an array of Article entities
        $articles = array_map(function ($articleData) {
            $article = new Article();
            $article->setTitle($articleData['title']);
            $article->setUrl($articleData['url'] ?? $articleData['link'] ?? null);
            $article->setSummary($articleData['summary'] ?? $articleData['description'] ?? null);
            $article->setPublishedAt(DateTimeImmutable::createFromMutable(new \DateTime($articleData['pubDate'] ?? $articleData['publishedAt'] ?? 'now')));
            return $article;
        }, $articles);

        // Save the articles to the database
        foreach ($articles as $article) {
            $entityManager->persist($article);
        }
        $entityManager->flush();

        // Check if the array of articles is empty
        if (empty($articles)) {
            return new JsonResponse(['message' => 'No articles found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Return a JSON response with a success message, status code 200, and code_http field
        return new JsonResponse([
            'message' => 'Articles successfully saved to the database',
            'code_http' => 200
        ], JsonResponse::HTTP_OK);    }

}