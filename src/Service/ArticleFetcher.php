<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class ArticleFetcher
{
    private HttpClientInterface $client;
    private CacheItemPoolInterface $cache;

    public function __construct(HttpClientInterface $client, CacheItemPoolInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function fetchRssArticles(string $url): array
    {
        $cacheKey = 'rss_articles_' . md5($url);

        // Vérifier si l'élément est en cache
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $response = $this->client->request('GET', $url);
        $content = $response->getContent();
        $rss = simplexml_load_string($content);

        $articles = [];

        foreach ($rss->channel->item as $item) {
            // Convert SimpleXMLElement to associative array
            $articleData = json_decode(json_encode($item), true);

            $articles[] = $articleData;

        }

        // Stocker les articles en cache avec une durée de vie de 3600 secondes (1 heure)
        $cacheItem->set($articles)->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $articles;
    }

    public function fetchJsonArticles(string $url): array
    {
        $cacheKey = 'json_articles_' . md5($url);

        // Vérifier si l'élément est en cache
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $response = $this->client->request('GET', $url);
        $jsonContent = $response->getContent();
        $jsonArticles = json_decode($jsonContent, true);

        // Stocker les articles JSON en cache avec une durée de vie de 3600 secondes (1 heure)
        $cacheItem->set($jsonArticles)->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $jsonArticles;
    }

    public function fetchLocalArticles(string $filePath): array
    {
        // Create a cache key based on the file path
        $cacheKey = md5($filePath);

        // Retrieve the cache adapter
        $cache = new FilesystemAdapter();

        // Check if the data is cached
        $cachedItem = $cache->getItem($cacheKey);
        if ($cachedItem->isHit()) {
            // Return cached data if available
            return $cachedItem->get();
        }

        // If not cached, read the file and parse JSON
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Le fichier spécifié n\'existe pas.');
        }

        $fileContent = file_get_contents($filePath);
        $localArticles = json_decode($fileContent, true);

        if (!is_array($localArticles)) {
            throw new \RuntimeException('Le fichier local ne contient pas de données valides.');
        }

        // Cache the parsed articles
        $cachedItem->set($localArticles);
        $cache->save($cachedItem);

        return $localArticles;
    }
}
