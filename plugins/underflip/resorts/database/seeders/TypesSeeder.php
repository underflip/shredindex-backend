<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Seeder;
use Google_Client;
use Google_Service_Sheets;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Models\TypeValue;
use Underflip\Resorts\Models\Unit;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\TotalScore;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Generic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @codeCoverageIgnore
 */
class TypesSeeder extends Seeder implements Downable
{
    protected $spreadsheetId;
    protected $logFile;

    public function __construct(string $spreadsheetId = '1l_KlxfKpzzD6zq8A2ZnX4jzzzvlARXhYCIazSUMtdrc', string $logFile = 'types_log.json')
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->logFile = $logFile;
    }

    /**
     * Query an existing unit by name
     *
     * @throws \Exception
     */
    protected function getUnitByName(string $name)
    {
        $unit = Unit::where('name', $name)->first();

        if (!$unit) {
            Log::error('Unable to query required Underflip\Resorts\Models\Unit', ['name' => $name]);
            throw new \Exception(sprintf('Unable to query required Underflip\Resorts\Models\Unit: "%s"', $name));
        }

        return $unit;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        try {
            Log::info('Starting TypesSeeder run.');

            // Google Sheets setup
            $client = new Google_Client();
            $client->setApplicationName('Google Sheets API');
            $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('credentials.json')); // Adjust the path to your credentials
            $client->setAccessType('offline');

            $service = new Google_Service_Sheets($client);

            // Fetch data from the getSheetData
            $typesValues = $this->getSheetData($service, $this->spreadsheetId, 'Types!A1:H10000');
            $this->processTypes($typesValues);
            app(TotalScore::class)->findOrCreateType();

            Log::info('TypesSeeder run completed successfully.');
        } catch (Exception $e) {
            Log::error('TypesSeeder run failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getSheetData($service, $spreadsheetId, $range)
    {
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            Log::error('No data found in the Google Sheet.');
            throw new Exception('No data found in the Google Sheet.');
        }

        Log::info('Data fetched from Google Sheet.', ['range' => $range, 'values' => count($values)]);
        return $values;
    }

    protected function processTypes($values)
    {
        $addedTypes = [];
        $categoryMapping = [
            'rating' => Rating::class,
            'numeric' => Numeric::class,
            'generic' => Generic::class,
        ];

        $score = $this->getUnitByName('score');
        $meters = $this->getUnitByName('meter');
        $total = $this->getUnitByName('total');
        $percentage = $this->getUnitByName('percentage');

        $unitIdMapping = [
            'score' => $score->id,
            'meters' => $meters->id,
            'percentage' => $percentage->id,
            'total' => $total->id,
        ];

        foreach ($values as $row) {
            if ($row[0] == 'id') {
                // Skip header row
                continue;
            }

            $typeId = is_numeric($row[0]) ? intval($row[0]) : 0;
            if ($typeId === 0) {
                Log::warning('Skipping invalid type_id.', ['row' => $row]);
                continue; // Skip if type_id is not valid
            }

            // Check if the type name is unique
            $typeName = $row[2] ?? 'default_name';
            $existingType = Type::where('name', $typeName)->first();
            if ($existingType) {
                Log::info('Skipping type because the name is not unique.', ['name' => $typeName]);
                continue;
            }

            $categoryClass = $categoryMapping[$row[1]] ?? Rating::class;
            $unitId = isset($row[5]) ? ($unitIdMapping[$row[5]] ?? null) : null;

            $type = Type::updateOrCreate(
                ['id' => $typeId],
                [
                    'name' => $typeName,
                    'title' => $row[3] ?? 'Default Title',
                    'category' => $categoryClass,
                    'unit_id' => $unitId,
                    'icon' => $row[4] ?? null,
                    'max_value' => $row[7] ?? null,
                ]
            );

            // Log the added type
            Log::info('Type added or updated.', [
                'id' => $type->id,
                'name' => $type->name,
                'title' => $type->title,
                'category' => $type->category,
                'unit_id' => $type->unit_id,
                'icon' => $type->icon,
                'max_value' => $type->max_value,
            ]);

            $addedTypes[] = $type->id;
        }

        $types = Type::all();

        // Save the log of added types
        Storage::disk('local')->put($this->logFile, json_encode($addedTypes));
        Log::info('Types processed and logged.', ['types' => $addedTypes]);
        Log::info('All Types processed and logged.', ['type' => $types]);
    }

    public function down()
    {
        // Retrieve the log of added types
        $addedTypes = [];
        if (Storage::disk('local')->exists($this->logFile)) {
            $addedTypes = json_decode(Storage::disk('local')->get($this->logFile), true);

            // Delete the logged types
            Type::whereIn('id', $addedTypes)->delete();

            // Remove the log file itself
            Storage::disk('local')->delete($this->logFile);
            Log::info('Types deleted and log file removed.', ['types' => $addedTypes]);
        }
    }
}
