<?php

namespace ApiBundle\Services;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DocumentService
{
    protected $client;
    
    protected $cache;
    
    protected $cache_key = 'document.csv.export';
    
    protected $limit = 500;
    
    protected $type = 'INVOICE';
    
    protected $page = 1;

    protected $response = null;
    
    protected $documents = [];
    
    public function __construct($client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->cache_key = $this->cache_key . microtime();
    }
    
    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function get()
    {
        $response = $this->fetchDocuments($this->page);

        $this->cacheResponse($response);

        while($response->page < $response->pages) {
            $response = $this->fetchDocuments($response->page+1);
            
            $this->cacheResponse($response);
        }

        $data = $this->getCache();
        
        $this->clearCache();
        
        return $data;
    }

    public function fetchDocuments($page = 1, $limit = null)
    {
        $this->response = $this->client->getDocuments([
            'page' => $page,
            'limit' => $this->limit ?: $limit,
            'type' => $this->type,
        ]);

        if(! $this->response->total) {
            throw new ResourceNotFoundException('No documents found...');
        }

        return $this->response;
    }

    public function cacheResponse($response)
    {
        $items = (array) $response->items;

        if($cached_data = $this->getCache()) {
            $data_to_cache = array_merge($cached_data, $items);
        } else {
            $data_to_cache = $items;
        }

        $this->setCache($data_to_cache);
    }

    public function setCache($data)
    {
        return $this->cache->set($this->cache_key, $data);
    }
    
    public function getCache()
    {
        return $this->cache->get($this->cache_key);
    }
    
    public function clearCache()
    {
        return $this->cache->deleteItem($this->cache_key);
    }
}