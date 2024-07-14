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

        $resorts = Resort::all();

        foreach ($resorts as $resort) {
            $params = [
                'index' => 'resorts',
                'id'    => $resort->id,
                'body'  => $resort->toArray()
            ];

            $client->index($params);
            $this->info("Indexed resort: {$resort->title}");
        }

        $this->info('All resorts have been indexed.');
    }
}
