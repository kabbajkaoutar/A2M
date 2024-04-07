<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ArticleFetcher;
use App\Entity\Article;

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

        // Define article sources
        $sources = [
            "https://www.lemonde.fr/rss/une.xml" => 'fetchRssArticles',
            "https://api.spaceflightnewsapi.net/v3/articles" => 'fetchJsonArticles',
            "https://saurav.tech/NewsAPI/top-headlines/category/health/fr.json" => 'fetchJsonArticles',
            "https://newsapi.org/v2/top-headlines?country=fr&amp;apiKey=API_KEY" => 'fetchJsonArticles',
            '../WS/articles.json' => 'fetchLocalArticles'
        ];
        // Fetch articles from the RSS feed and JSON API
        $articles = [];
        foreach ($sources as $source => $method) {
            try {
                $fetchedArticles = $articleFetcher->$method($source);
                if (is_array($fetchedArticles)) {
                    $articles = array_merge($articles, $fetchedArticles);
                }
            } catch (\Exception $e) {
                // Log error or handle it as per your requirements
                continue; // Skip this source if there's an error
            }
        }
        // Filter out non-array elements
        $cleanedArray = array_filter($articles, function ($item) {
            return is_array($item);

        });

        foreach ($cleanedArray as $key => $value) {
            if (is_string($key)) {
                $extractedValues = $cleanedArray[$key];
                unset($cleanedArray[$key]);
                $articles = array_merge($cleanedArray, $extractedValues);

            } }


        // Convert the array of articles into an array of Article entities
        foreach ($articles as $articleData) {
            $article = new Article();
            $article->setContext($articleData); // Store article data in the 'context' field
            // Persist the Article entity
            $entityManager->persist($article);
        }
        $entityManager->flush();

        // Check if the array of articles is empty
        if (empty($articles)) {
            return new JsonResponse(['message' => 'No articles found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Return a JSON response with a success message, status code 200, and code_http field
        return new JsonResponse([
            'message' => 'Opération réussie : les articles ont été enregistrés avec succès',
            'code_http' => 200
        ], JsonResponse::HTTP_OK);    }

}