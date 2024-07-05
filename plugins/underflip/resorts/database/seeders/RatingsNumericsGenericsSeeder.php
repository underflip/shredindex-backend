<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Seeder;
use Google_Client;
use Google_Service_Sheets;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Type;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 *
 * @codeCoverageIgnore
 */

class RatingsNumericsGenericsSeeder extends Seeder implements Downable
{
    protected $spreadsheetId = '1l_KlxfKpzzD6zq8A2ZnX4jzzzvlARXhYCIazSUMtdrc';

    public function run()
    {
        Log::info('Starting RatingsNumericsGenericsSeeder...');

        ini_set('memory_limit', '1024M'); // Increase memory limit

        try {
            $client = new Google_Client();
            $client->setApplicationName('Google Sheets API');
            $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('credentials.json')); // Adjust the path to your credentials
            $client->setAccessType('offline');

            Log::info('Google Client created successfully.');

            $service = new Google_Service_Sheets($client);
            Log::info('Google Sheets Service created successfully.');

            $ratingsValues = $this->getSheetData($service, 'Ratings!A1:Z10000000');
            $numericsValues = $this->getSheetData($service, 'Numerics!A1:Z1000000');
            // $genericsValues = $this->getSheetData($service, 'Generics!A1:Z10000');

            Log::info('Sheet data retrieved successfully.');

            DB::transaction(function () use ($ratingsValues, $numericsValues) {
                $this->processRatings($ratingsValues, 100); // Process ratings in batches of 100
                Log::info('Ratings processed successfully.');

                $this->processNumerics($numericsValues, 100); // Process numerics in batches of 100
                Log::info('Numerics processed successfully.');

                // $this->processGenerics($genericsValues);
                // Log::info('Generics processed successfully.');

                Log::info('Calling updateTotalScores...');
                $this->updateTotalScores();
                Log::info('Finished RatingsNumericsGenericsSeeder.');
            });
        } catch (Exception $e) {
            Log::error('Error in RatingsNumericsGenericsSeeder: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    protected function getSheetData($service, $range)
    {
        try {
            Log::info('Retrieving sheet data for range: ' . $range);
            $response = $service->spreadsheets_values->get($this->spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                Log::error('No data found in the Google Sheet for range: ' . $range);
                throw new Exception('No data found in the Google Sheet.');
            }

            Log::info('Data retrieved successfully for range: ' . $range);
            return $values;
        } catch (Exception $e) {
            Log::error('Error retrieving sheet data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    protected function processRatings($values, $batchSize = 100)
    {
        Log::info('Processing ratings...');
        $types = Type::where('category', Rating::class)->pluck('id', 'name');

        if ($types->isEmpty()) {
            Log::error('No existing Types for Rating.');
            throw new Exception(sprintf(
                'There are no existing Types (%s) to rate. Try refreshing the Resorts plugin to seed Types.',
                Type::class
            ));
        }

        $chunks = array_chunk($values, $batchSize);

        foreach ($chunks as $chunk) {
            $ratingsBatch = [];
            foreach ($chunk as $row) {
                if ($row[0] == 'id') {
                    continue;
                }

                try {
                    Log::info('Processing rating row', ['row' => $row]);

                    $resortId = isset($row[2]) && is_numeric($row[2]) ? intval($row[2]) : 0;
                    $value = isset($row[1]) && is_numeric($row[1]) ? min(intval($row[1]), 100) : rand(1, 5);
                    $typeName = isset($row[4]) ? $row[4] : '';
                    $typeId = isset($types[$typeName]) ? $types[$typeName] : null;

                    if (!$typeId) {
                        Log::warning('Type not found for rating', ['type_name' => $typeName]);
                        continue;
                    }

                    if (Resort::find($resortId)) {
                        $ratingsBatch[] = [
                            'resort_id' => $resortId,
                            'value' => $value,
                            'type_id' => $typeId,
                        ];
                    } else {
                        Log::warning('Resort not found for rating', ['resort_id' => $resortId]);
                    }
                } catch (Exception $e) {
                    Log::error('Error processing row', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            if (!empty($ratingsBatch)) {
                Log::info('Inserting ratings batch into database', ['batch_size' => $batchSize]);
                try {
                    Rating::insert($ratingsBatch);
                } catch (Exception $e) {
                    Log::error('Error inserting ratings batch into database', ['error' => $e->getMessage()]);
                }
            }
        }

        Log::info('Finished processing ratings in batches.');
    }

    protected function processNumerics($values, $batchSize = 100)
    {
        Log::info('Processing numerics...');
        $types = Type::where('category', Numeric::class)->pluck('id', 'name');

        $chunks = array_chunk($values, $batchSize);

        foreach ($chunks as $chunk) {
            $numericsBatch = [];
            foreach ($chunk as $row) {
                if ($row[0] == 'id') {
                    continue;
                }

                try {
                    Log::info('Processing numeric row', ['row' => $row]);

                    $resortId = isset($row[1]) && is_numeric($row[1]) ? intval($row[1]) : 1;
                    $value = isset($row[2]) && is_numeric($row[2]) ? intval($row[2]) : rand(0, 100);
                    $typeId = isset($row[3]) && is_numeric($row[3]) ? intval($row[3]) : 1;
                    $typeName = isset($row[4]) ? $row[4] : '';
                    $typeId = isset($types[$typeName]) ? $types[$typeName] : null;

                    if (Resort::find($resortId)) {
                        $numericsBatch[] = [
                            'resort_id' => $resortId,
                            'value' => $value,
                            'type_id' => $typeId,
                        ];
                    } else {
                        Log::warning('Resort not found for numeric', ['resort_id' => $resortId]);
                    }
                } catch (Exception $e) {
                    Log::error('Error processing numeric row', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            if (!empty($numericsBatch)) {
                Log::info('Inserting numerics batch into database', ['batch_size' => $batchSize]);
                try {
                    Numeric::insert($numericsBatch);
                } catch (Exception $e) {
                    Log::error('Error inserting numerics batch into database', ['error' => $e->getMessage()]);
                }
            }
        }

        Log::info('Finished processing numerics in batches.');
    }

    protected function processGenerics($values, $batchSize = 100)
    {
        Log::info('Processing generics...');

        $chunks = array_chunk($values, $batchSize);

        foreach ($chunks as $chunk) {
            $genericsBatch = [];
            foreach ($chunk as $row) {
                if ($row[0] == 'id') {
                    continue;
                }

                try {
                    Log::info('Processing row', ['row' => $row]);

                    $resortId = isset($row[1]) && is_numeric($row[1]) ? intval($row[1]) : 0;
                    $value = isset($row[2]) ? $row[2] : 'Unknown';
                    $typeId = isset($row[3]) && is_numeric($row[3]) ? intval($row[3]) : 1;

                    if (Resort::find($resortId)) {
                        $genericsBatch[] = [
                            'resort_id' => $resortId,
                            'value' => $value,
                            'type_id' => $typeId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        Log::warning('Resort not found for generic', ['resort_id' => $resortId]);
                    }
                } catch (Exception $e) {
                    Log::error('Error processing generic row', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            if (!empty($genericsBatch)) {
                Log::info('Inserting generics batch into database', ['batch_size' => $batchSize]);
                try {
                    Generic::insert($genericsBatch);
                } catch (Exception $e) {
                    Log::error('Error inserting generics batch into database', ['error' => $e->getMessage()]);
                }
            }
        }

        Log::info('Finished processing generics in batches.');
    }

    protected function updateTotalScores()
    {
        $resorts = Resort::all();
        Log::info('Updating Total Scores...');

        foreach ($resorts as $resort) {
            $resort->updateTotalScore();
        }
    }

    public function down()
    {
        Rating::query()->truncate();
        Numeric::query()->truncate();
        Generic::query()->truncate();
    }
}
