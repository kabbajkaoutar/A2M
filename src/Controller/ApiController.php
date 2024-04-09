<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ArticleFetcher;
use App\Repository\ArticleRepository;
use App\Entity\Article;


class ApiController extends AbstractController
{
    //cette fonction permet de recuperer l'utilisateur connecte
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login()
    {
        return $this->json([
            'user' => $this->getUser(),
        ]);
    }

    //cette fonction permet de se deconnecter et supprime le cookie Bearer ainsi le token n'est plus valide
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): Response
    {
        $response = new Response();
        $response->headers->clearCookie('Bearer');

        // Pour supprimer le token du header Authorization (si nécessaire)
        $response->headers->remove('Authorization');

        return $response;
    }



// cette fonction permet de recuperer les articles depuis differentes ressources
    #[Route('/api/chargeArticles', name: 'api_chargeArticles', methods: ['GET'])]
    public function chargeArticles(ArticleFetcher $articleFetcher, EntityManagerInterface $entityManager): JsonResponse
    {
        // Définition des sources d'articles
        $sources = [
            "https://www.lemonde.fr/rss/une.xml" => 'fetchRssArticles',
            "https://api.spaceflightnewsapi.net/v3/articles" => 'fetchJsonArticles',
            "https://saurav.tech/NewsAPI/top-headlines/category/health/fr.json" => 'fetchJsonArticles',
            "https://newsapi.org/v2/top-headlines?country=fr&amp;apiKey=API_KEY" => 'fetchJsonArticles',
            '../WS/articles.json' => 'fetchLocalArticles'
        ];

// Récupération des articles à partir du flux RSS et de l'API JSON
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

// Filtrage des éléments non-array
        $cleanedArray = array_filter($articles, function ($item) {
            return is_array($item);
        });

// Exclusion des clés de chaîne de caractères du tableau
        foreach ($cleanedArray as $key => $value) {
            if (is_string($key)) {
                $extractedValues = $cleanedArray[$key];
                unset($cleanedArray[$key]);
                $articles = array_merge($cleanedArray, $extractedValues);
            }
        }

// Nettoyage des articles (troncature de la description à 290 caractères)

        foreach ($articles as $key => &$value) {
            if (is_string($value) && strlen($value) > 290) {
                $value = substr($value, 0, 290) . '...';
            }
        }

// Conversion du tableau d'articles en un tableau d'entités Article
// Début de la transaction
        $entityManager->beginTransaction();

        try {
            foreach ($articles as $articleData) {
                $article = new Article();
                $article->setContext($articleData);
                $entityManager->persist($article);
            }

            // Exécution des requêtes SQL persistées dans la transaction
            $entityManager->flush();

            // Validation de la transaction
            $entityManager->commit();

            // Vérifie si le tableau d'articles est vide
            if (empty($articles)) {
                return new JsonResponse(['message' => 'Aucun article trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Retourne une réponse JSON en cas de succès
            return new JsonResponse([
                'message' => 'Opération réussie : les articles ont été enregistrés avec succès',
                'code_http' => JsonResponse::HTTP_OK
            ]);

        } catch (\Exception $e) {
            // En cas d'erreur, annule la transaction
            $entityManager->rollback();

            // Retourne une réponse JSON avec un message d'erreur
            return new JsonResponse([
                'message' => 'Une erreur est survenue lors de l\'enregistrement des articles',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


    #[Route('/articles', name: 'articles_list', methods: ['GET'])]

    public function list(): Response
    {
        return $this->render('article/list.html.twig');
    }
    #[Route('/articles', name: 'articles_index', methods: ['GET'])]
     public function index()
    {
        return $this->render('article/list.html.twig');
    }


    #[Route('/api/listArticles', name: 'api_listArticles', methods: ['POST'])]
    public function listArticles(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        // Retrieve pagination parameters from DataTables request
        $data = json_decode($request->getContent(), true);
        $draw = $data['draw'] ?? 1 ; // Draw counter (used by DataTables)
        $start = $data['start'] ?? 0 ; // Draw counter (used by DataTables)
        $length = $data['length'] ?? 10; // Draw counter (used by DataTables)
        $searchValue = $data['search']['value'] ?? null; // Search value entered by the user

        // Fetch paginated data from the repository, applying search if provided
        $articles = $articleRepository->findPaginated($start, $length, $searchValue);


        // Prepare the response data in DataTables format
        $data = [
            'draw' => $draw,
            'recordsTotal' => $articleRepository->countAll(), // Total number of records (without filtering)
            'recordsFiltered' => $articleRepository->countAll(), // Total number of records after filtering (if needed)
            'data' => [],
        ];

        foreach ($articles as $article) {
            $data['data'][] = [
                'id' => $article->getId(),
                'context' => $article->getContext(),
                // Add more properties here if needed
            ];
        }

        // Return the paginated data as a JSON response
        return new JsonResponse($data);
    }

}