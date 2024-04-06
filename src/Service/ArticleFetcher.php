<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Cache\CacheItemPoolInterface;

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
            $article = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => (string) $item->description,
                'pubDate' => (string) $item->pubDate
            ];
            $articles[] = $article;
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
}
