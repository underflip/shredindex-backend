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
        $locationsValues = $this->getSheetData($service, $spreadsheetId, 'Locations!A1:Z10000');
        $commentsValues = $this->getSheetData($service, $spreadsheetId, 'Comments!A1:Z10000');
        $continentsValues = $this->getSheetData($service, $spreadsheetId, 'Continents!A1:Z10000');

        // Process data from each sheet
        $this->processContinents($continentsValues);
        $this->processResorts($resortsValues);
        $this->processLocation($locationsValues);
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
            $resort->id = $row[0];
            $resort->title = $row[1];
            $resort->url_segment = $row[2] ?? $row[0];
            $resort->affiliate_url = $row[2] ?? 'https://example.com';
            $resort->description = $row[6] ?? 'No description available.';
            $resort->save();

            Log::info('Added in', ['Resort id' => $row[0], 'Resort name' => $resort->title]);
        }
    }

    protected function processLocation($values)
    {
        foreach ($values as $row) {
            if ($row[0] == 'id') {
                // Skip header row
                continue;
            }

            $location = new Location();
            $location->resort_id = is_numeric($row[1]) ? $row[1] : 0;
            $location->address = $row[2] ?? 'Unknown address';
            $location->city = $row[3] ?? 'Unknown city';
            $location->zip = $row[4] ?? '00000';
            $location->latitude = is_numeric($row[5]) ? $row[5] : 0;
            $location->longitude = is_numeric($row[6]) ? $row[6] : 0;
            $location->country_id = is_numeric($row[7]) ? $row[7] : 1;
            $location->state_id = isset($row[12]) && is_numeric($row[12]) ? $row[12] : 1;
            $location->vicinity = $row[4] ?? 'Unknown vicinity';
            $location->save();

            // Continents
            $country = Country::where('id', $row[7])->first() ?? Country::inRandomOrder()->first();
            $continentCode = $this->continentService->getContinentCode($country->code) ?? 'UNKNOWN_CODE';
            $continent = Continent::where('code', $continentCode)->first();


            if ($continent) {
                $location->continent()->associate($continent);
                $location->save();
            }
            Log::info('Added in', [
                'Location id' => $location->id,
                'State id' => $location->state_id,
                'Continent' => $continent
            ]);
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
