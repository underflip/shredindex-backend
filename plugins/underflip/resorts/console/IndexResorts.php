<?php

namespace Underflip\Resorts\Console;

use Illuminate\Console\Command;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Classes\ElasticSearchService;

class IndexResorts extends Command
{
    protected $signature = 'resorts:index';
    protected $description = 'Index all resorts in Elasticsearch';

    public function handle()
    {
        $esClient = new ElasticSearchService();
        $client = $esClient->getClient();

        $resorts = Resort::with(['location.continent', 'location.country', 'location.state'])->get();

        foreach ($resorts as $resort) {
            $params = [
                'index' => 'resorts',
                'id'    => $resort->id,
                'body'  => [
                    'title' => $resort->title,
                    'description' => $resort->description,
                    'location' => [
                        'continent' => [
                            'name' => $resort->location->continent->name ?? null
                        ],
                        'country' => [
                            'name' => $resort->location->country->name ?? null
                        ],
                        'state' => [
                            'name' => $resort->location->state->name ?? null
                        ],
                        'city' => $resort->location->city
                    ]
                    // ... other fields ...
                ]
            ];

            $client->index($params);
            $this->info("Indexed resort: {$resort->title}");
        }

        $this->info('All resorts have been indexed.');
    }
}
