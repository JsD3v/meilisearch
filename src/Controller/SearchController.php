<?php

namespace App\Controller;

use Exception;
use MeiliSearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    private Client $client;
    private string $prefix;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $url = $parameterBag->get('app.meilisearch.url');
        $apiKey = $parameterBag->get('app.meilisearch.api_key');
        $this->prefix = $parameterBag->get('app.meilisearch.prefix');

        $this->client = new Client($url, $apiKey);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/app/meilisearch/search', name: 'app_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type');
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $searchOptions = [
            'limit' => $limit,
            'offset' => $offset
        ];

        $results = [];

        try {
            if (!empty($query)) {
                // Recherche selon le type spécifié
                if ($type === 'products') {
                    $index = $this->client->index($this->prefix . 'products');
                    $searchResults = $index->search($query, $searchOptions);
                    $results['products'] = $searchResults->getHits();
                } elseif ($type === 'articles') {
                    $index = $this->client->index($this->prefix . 'articles');
                    $searchResults = $index->search($query, $searchOptions);
                    $results['articles'] = $searchResults->getHits();
                } else {
                    // Recherche globale
                    $productIndex = $this->client->index($this->prefix . 'products');
                    $productResults = $productIndex->search($query, $searchOptions);
                    $results['products'] = $productResults->getHits();

                    $articleIndex = $this->client->index($this->prefix . 'articles');
                    $articleResults = $articleIndex->search($query, $searchOptions);
                    $results['articles'] = $articleResults->getHits();
                }
            }

            return $this->render('search/index.html.twig', [
                'query' => $query,
                'type' => $type,
                'results' => $results,
                'page' => $page
            ]);
        } catch (Exception $e) {
            // En cas d'erreur avec MeiliSearch
            return $this->render('search/index.html.twig', [
                'query' => $query,
                'type' => $type,
                'results' => [],
                'page' => $page,
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/meilisearch/search/api', name: 'app_meilisearch_search_api')]
    public function searchApi(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', null);
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $searchOptions = [
            'limit' => $limit,
            'offset' => $offset
        ];

        $results = [];

        try {
            if (!empty($query)) {
                if ($type === 'products') {
                    $index = $this->client->index($this->prefix . 'products');
                    $searchResults = $index->search($query, $searchOptions);
                    $results = [
                        'hits' => $searchResults->getHits(),
                        'totalHits' => $searchResults->getEstimatedTotalHits(),
                        'totalPages' => ceil($searchResults->getEstimatedTotalHits() / $limit),
                        'currentPage' => $page
                    ];
                } elseif ($type === 'articles') {
                    $index = $this->client->index($this->prefix . 'articles');
                    $searchResults = $index->search($query, $searchOptions);
                    $results = [
                        'hits' => $searchResults->getHits(),
                        'totalHits' => $searchResults->getEstimatedTotalHits(),
                        'totalPages' => ceil($searchResults->getEstimatedTotalHits() / $limit),
                        'currentPage' => $page
                    ];
                } else {
                    // Recherche globale
                    $productIndex = $this->client->index($this->prefix . 'products');
                    $productResults = $productIndex->search($query, $searchOptions);

                    $articleIndex = $this->client->index($this->prefix . 'articles');
                    $articleResults = $articleIndex->search($query, $searchOptions);

                    $results = [
                        'products' => $productResults->getHits(),
                        'articles' => $articleResults->getHits(),
                        'totalProductHits' => $productResults->getEstimatedTotalHits(),
                        'totalArticleHits' => $articleResults->getEstimatedTotalHits(),
                        'totalHits' => $productResults->getEstimatedTotalHits() + $articleResults->getEstimatedTotalHits(),
                        'currentPage' => $page
                    ];
                }
            }

            return $this->json($results);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}