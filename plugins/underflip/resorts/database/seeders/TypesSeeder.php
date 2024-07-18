<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Seeder;
use Google_Client;
use Google_Service_Sheets;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Models\TypeGroup;
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
            $typeGroupValues = $this->getSheetData($service, $this->spreadsheetId, 'TypeGroup!A1:C10000');
            $this->processTypeGroups($typeGroupValues);

            $typesValues = $this->getSheetData($service, $this->spreadsheetId, 'Types!A1:I10000');
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

    protected function processTypeGroups($values)
    {
        $addedTypeGroups = [];

        foreach ($values as $row) {
            if ($row[0] == 'name') {
                // Skip header row
                continue;
            }

            $typeGroupId = is_numeric($row[2]) ? intval($row[2]) : 0;
            if ($typeGroupId === 0) {
                Log::warning('Skipping invalid type_group_id.', ['row' => $row]);
                continue; // Skip if type_group_id is not valid
            }

            $typeGroup = TypeGroup::updateOrCreate(
                ['id' => $typeGroupId],
                [
                    'name' => $row[0] ?? 'default_name',
                    'title' => $row[1] ?? 'Default Title',
                ]
            );

            // Log the added type group
            Log::info('TypeGroup added or updated.', [
                'id' => $typeGroup->id,
                'name' => $typeGroup->name,
                'title' => $typeGroup->title,
            ]);

            $addedTypeGroups[] = $typeGroup->id;
        }

        // Save the log of added type groups
        $existingLog = Storage::disk('local')->exists($this->logFile)
            ? json_decode(Storage::disk('local')->get($this->logFile), true)
            : [];
        $existingLog['type_groups'] = $addedTypeGroups;
        Storage::disk('local')->put($this->logFile, json_encode($existingLog));
        Log::info('TypeGroups processed and logged.', ['type_groups' => $addedTypeGroups]);
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
                    'max_value' => isset($row[7]) && is_numeric($row[7]) ? $row[7] : null,
                    'type_group_id' => isset($row[8]) && is_numeric($row[8]) ? intval($row[8]) : null,
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
                'type_group_id' => $type->type_group_id,
            ]);

            $addedTypes[] = $type->id;
        }

        $types = Type::all();

        // Update the log file with both types and type groups
        $existingLog = json_decode(Storage::disk('local')->get($this->logFile), true);
        $existingLog['types'] = $addedTypes;
        Storage::disk('local')->put($this->logFile, json_encode($existingLog));
        Log::info('Types and TypeGroups processed and logged.', ['types' => $addedTypes, 'type_groups' => $existingLog['type_groups'] ?? []]);
        Log::info('All Types processed and logged.', ['type' => $types]);
    }

    public function down()
    {
        // Retrieve the log of added types and type groups
        if (Storage::disk('local')->exists($this->logFile)) {
            $log = json_decode(Storage::disk('local')->get($this->logFile), true);

            // Delete the logged types
            if (isset($log['types'])) {
                Type::whereIn('id', $log['types'])->delete();
                Log::info('Types deleted.', ['types' => $log['types']]);
            }

            // Delete the logged type groups
            if (isset($log['type_groups'])) {
                TypeGroup::whereIn('id', $log['type_groups'])->delete();
                Log::info('TypeGroups deleted.', ['type_groups' => $log['type_groups']]);
            }

            // Remove the log file itself
            Storage::disk('local')->delete($this->logFile);
            Log::info('Log file removed.');
        }
    }
}
