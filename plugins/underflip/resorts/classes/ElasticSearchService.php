<?php

namespace Underflip\Resorts\Classes;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearchService
{
   protected $client;

   public function __construct()
   {
       $this->client = ClientBuilder::create()
           ->setHosts(['elasticsearch:9200'])
           ->build();
   }

   public function getClient()
   {
       return $this->client;
   }

   public function searchResorts($query, $from = 0, $size = 10)
      {
          $params = [
              'index' => 'resorts',
              'body' => [
                  'from' => $from,
                  'size' => $size,
                  'query' => [
                      'bool' => [
                          'should' => [
                              [
                                  'match_phrase_prefix' => [
                                      'title' => [
                                          'query' => $query,
                                          'boost' => 4
                                      ]
                                  ]
                              ],
                              [
                                  'match_phrase_prefix' => [
                                      'location.continent.name' => [
                                          'query' => $query,
                                          'boost' => 3
                                      ]
                                  ]
                              ],
                              [
                                  'match_phrase_prefix' => [
                                      'location.country.name' => [
                                          'query' => $query,
                                          'boost' => 3
                                      ]
                                  ]
                              ],
                              [
                                  'match_phrase_prefix' => [
                                      'location.state.name' => [
                                          'query' => $query,
                                          'boost' => 2
                                      ]
                                  ]
                              ],
                              [
                                  'match_phrase_prefix' => [
                                      'location.city' => [
                                          'query' => $query,
                                          'boost' => 2
                                      ]
                                  ]
                              ],
                              [
                                  'wildcard' => [
                                      'description' => "*$query*"
                                  ]
                              ]
                          ]
                      ]
                  ]
              ]
          ];

          try {
              $response = $this->client->search($params);
              \Log::info('Elasticsearch response', ['total' => $response['hits']['total']['value']]);
              return $response;
          } catch (\Exception $e) {
              \Log::error('Elasticsearch search error: ' . $e->getMessage(), ['exception' => $e]);
              throw new \Exception('An error occurred while searching for resorts: ' . $e->getMessage());
          }
      }
}
