<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Seeder;
use DB;
use Google_Client;
use Google_Service_Sheets;
use Underflip\Resorts\Models\Comment;
use Underflip\Resorts\Models\Location;
use Underflip\Resorts\Models\Continent;
use Underflip\Resorts\Models\Unit;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\ResortImage;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Classes\ContinentService;
use RainLab\Location\Models\Country;
use RainLab\Location\Models\State;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class ResortsSeederFromSheets extends Seeder implements Downable
{
    protected ContinentService $continentService;
    protected $locationsValues;

    public function __construct()
    {
        $this->continentService = new ContinentService(); // Instantiate manually
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        // Google Sheets setup
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets API');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(storage_path('credentials.json')); // Adjust the path to your credentials
        $client->setAccessType('offline');

        $service = new Google_Service_Sheets($client);
        $spreadsheetId = '1l_KlxfKpzzD6zq8A2ZnX4jzzzvlARXhYCIazSUMtdrc';

        // Fetch data from each sheet
        $resortsValues = $this->getSheetData($service, $spreadsheetId, 'Resorts!A1:Z10000');
        $this->locationsValues = $this->getSheetData($service, $spreadsheetId, 'Locations!A1:Z10000');
        $commentsValues = $this->getSheetData($service, $spreadsheetId, 'Comments!A1:Z10000');
        $continentsValues = $this->getSheetData($service, $spreadsheetId, 'Continents!A1:Z10000');

        // Process data from each sheet
        $this->processContinents($continentsValues);
        $this->processResorts($resortsValues);
        Log::info("Calling processImages function");
        $this->processComments($commentsValues);
    }

    protected function getSheetData($service, $spreadsheetId, $range)
    {
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            throw new Exception('No data found in the Google Sheet.');
        }

        return $values;
    }

    protected function processContinents($values)
    {
        foreach ($values as $row) {
            if ($row[0] == 'name') {
                // Skip header row
                continue;
            }

            $continent = new Continent();
            $continent->name = $row[0];
            $continent->code = $row[1] ?? 'UNKNOWN_CODE';
            $continent->save();
        }
    }

    protected function processResorts($values)
    {
        foreach ($values as $row) {
            if ($row[0] == 'id') {
                // Skip header row
                continue;
            }

            $resort = new Resort();
            $resort->title = $row[1];
            $resort->url_segment = $row[2] ?? $row[0];
            $resort->affiliate_url = $row[2] ?? 'https://example.com';
            $resort->description = $row[6] ?? 'No description available.';
            $resort->save();

            // Ensure resort has a location
            $locationRow = $this->findLocationRow($row[0]);
            if ($locationRow) {
                $this->processLocation($resort->id, $locationRow);
            } else {
                $this->createDefaultLocation($resort->id);
            }
        }
    }

    protected function findLocationRow($resortTitle)
    {
        foreach ($this->locationsValues as $row) {
            if ($row[0] == $resortTitle) {
                return $row;
            }
        }
        return null;
    }

    protected function processLocation($resortId, $row)
    {
        $country = Country::where('name', $row[8])->first() ?? Country::inRandomOrder()->first();
        $state = State::where('name', $row[9])->first() ?? State::inRandomOrder()->first();

        if (!$country) {
            throw new Exception('Country not found: ' . $row[8]);
        }

        if (!$state) {
            throw new Exception('State not found: ' . $row[9]);
        }

        $location = new Location();
        $location->address = $row[1] ?? 'Unknown address';
        $location->city = $row[2] ?? 'Unknown city';
        $location->zip = $row[3] ?? '00000';
        $location->country_id = $country->id;
        $location->state_id = $state->id;
        $location->latitude = is_numeric($row[6]) ? $row[6] : 0.0;
        $location->longitude = is_numeric($row[7]) ? $row[7] : 0.0;
        $location->vicinity = $row[8] ?? 'Unknown vicinity';
        $location->resort_id = $resortId;
        $location->save();

        // Continents
        $continentCode = $this->continentService->getContinentCode($country->code) ?? 'UNKNOWN_CODE';
        $continent = Continent::where('code', $continentCode)->first();

        if ($continent) {
            $location->continent()->associate($continent);
            $location->save();
        }
    }

    protected function createDefaultLocation($resortId)
    {
        $country = Country::inRandomOrder()->first();
        $state = State::where('country_id', $country->id)->inRandomOrder()->first();

        $location = new Location();
        $location->address = 'Unknown address';
        $location->city = 'Unknown city';
        $location->zip = '00000';
        $location->country_id = $country->id;
        $location->state_id = $state->id ?? null;
        $location->latitude = 0.0;
        $location->longitude = 0.0;
        $location->vicinity = 'Unknown vicinity';
        $location->resort_id = $resortId;
        $location->save();

        // Continents
        $continentCode = $this->continentService->getContinentCode($country->code) ?? 'UNKNOWN_CODE';
        $continent = Continent::where('code', $continentCode)->first();

        if ($continent) {
            $location->continent()->associate($continent);
            $location->save();
        }
    }

    protected function processComments($values)
    {
        foreach ($values as $row) {
            if ($row[0] == 'id') {
                // Skip header row
                continue;
            }

            $comment = new Comment();
            $comment->resort_id = is_numeric($row[1]) ? $row[1] : 0;
            $comment->comment = $row[2] ?? 'No comment.';
            $comment->author = $row[3] ?? 'Anonymous';
            $comment->save();
        }
    }

    public function down()
    {
        Resort::query()->truncate();
        Location::query()->truncate();
        Continent::query()->truncate();
        Comment::query()->truncate();
    }
}
