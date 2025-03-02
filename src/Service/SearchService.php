<?php

namespace App\Service;

use MeiliSearch\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SearchService
{
    private Client $client;
    private int $nbResults;

    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
        $url = $this->params->get('meilisearch.url');
        $apiKey = $this->params->get('meilisearch.api_key');
        $this->client = new Client($url, $apiKey);
        $this->nbResults = $this->params->get('meilisearch.nbResults', 12);
    }

    /**
     * Recherche globale sur les produits et articles
     */
    public function search(string $query, ?string $indexName = null, int $page = 1): array
    {
        $results = [];
        $limit = $this->nbResults;
        $offset = ($page - 1) * $limit;

        $searchOptions = [
            'limit' => $limit,
            'offset' => $offset,
            'attributesToHighlight' => ['*']
        ];

        if ($indexName) {
            // Recherche sur un index spécifique
            if (in_array($indexName, ['products', 'articles'])) {
                $index = $this->client->index($indexName);
                $searchResults = $index->search($query, $searchOptions);

                return [
                    'hits' => $searchResults->getHits(),
                    'totalHits' => $searchResults->getEstimatedTotalHits(),
                    'totalPages' => ceil($searchResults->getEstimatedTotalHits() / $limit),
                    'currentPage' => $page,
                    'query' => $query,
                ];
            }
        } else {
            // Recherche sur tous les indices (produits et articles)
            $allResults = [];
            $totalHits = 0;

            // Recherche dans les produits
            $productIndex = $this->client->index('products');
            $productResults = $productIndex->search($query, $searchOptions);
            $allResults['products'] = $productResults->getHits();
            $totalHits += $productResults->getEstimatedTotalHits();

            // Recherche dans les articles
            $articleIndex = $this->client->index('articles');
            $articleResults = $articleIndex->search($query, $searchOptions);
            $allResults['articles'] = $articleResults->getHits();
            $totalHits += $articleResults->getEstimatedTotalHits();

            return [
                'results' => $allResults,
                'totalHits' => $totalHits,
                'query' => $query,
            ];
        }

        return $results;
    }

    /**
     * Recherche dans les produits uniquement
     */
    public function searchProducts(string $query, int $page = 1): array
    {
        return $this->search($query, 'products', $page);
    }

    /**
     * Recherche dans les articles uniquement
     */
    public function searchArticles(string $query, int $page = 1): array
    {
        return $this->search($query, 'articles', $page);
    }
}