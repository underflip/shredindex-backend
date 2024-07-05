<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Seeder;
use Google_Client;
use Google_Service_Sheets;
use Underflip\Resorts\Models\ResortImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use DB;

/**
 * @codeCoverageIgnore
 */
class ResortsImageSeederFromSheets extends Seeder implements Downable
{
    protected $spreadsheetId;
    protected $logFile;

    public function __construct(string $spreadsheetId = '1l_KlxfKpzzD6zq8A2ZnX4jzzzvlARXhYCIazSUMtdrc', string $logFile = 'resorts_images_log.json')
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->logFile = $logFile;
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

        // Fetch data from the sheet
        $imagesValues = $this->getSheetData($service, $this->spreadsheetId, 'Images!A1:Z10000');
        $this->processImages($imagesValues);
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

    protected function processImages($values)
    {
        $addedFiles = [];

        foreach ($values as $row) {
            if ($row[0] == 'id') {
                // Skip header row
                continue;
            }

            $resortId = is_numeric($row[1]) ? intval($row[1]) : 0;
            if ($resortId === 0) {
                continue; // Skip if resort_id is not valid
            }

            $image = new ResortImage();
            $image->resort_id = $resortId;
            $image->name = $row[2] ?? 'default.jpg';
            $image->alt = $row[3] ?? 'Default image';
            $image->sort_order = is_numeric($row[4]) ? intval($row[4]) : null;
            $image->save();

            // Handle file association if applicable
            if (isset($row[2]) && !empty($row[2])) {
                $imageFilePath = $row[2]; // Assuming image URL is in column 2

                if (is_null($imageFilePath) || $imageFilePath === '') {
                    Log::error("Filepath is null or empty for resort_id: " . $resortId);
                    continue;
                }

                $baseUrl = $imageFilePath; // Get the first part (before the question mark)

                // Generate a unique image filename using resort_id and a hash
                $fileExtension = pathinfo($baseUrl, PATHINFO_EXTENSION);
                $uniqueFilename = $resortId . '_' . md5($baseUrl) . '.' . $fileExtension; // Use original extension
                $path = 'uploads/resorts/' . $uniqueFilename;

                // Download the image (using the baseUrl to avoid query parameters)
                try {
                    $contents = file_get_contents($baseUrl); // Use the extracted baseUrl for download
                    if ($contents !== false) {
                        // Save the image to the specified path
                        Storage::disk('local')->put($path, $contents);

                        // Now associate the image with the ResortImage model directly
                        $image->image()->createFromFile(
                            storage_path('app/' . $path),
                            ['file_name' => $uniqueFilename, 'content_type' => mime_content_type(storage_path('app/' . $path))]
                        );
                        $image->save(); // Save again after associating the image

                        // Log the added file
                        $addedFiles[] = $path;
                    } else {
                        Log::error("Error downloading image: " . $imageFilePath);
                    }
                } catch (Exception $e) {
                    Log::error("Error downloading image: " . $imageFilePath . " - " . $e->getMessage());
                }
            }
        }

        // Save the log of added files
        Storage::disk('local')->put($this->logFile, json_encode($addedFiles));
    }

    public function down()
    {
        // Retrieve the log of added files
        $addedFiles = [];
        if (Storage::disk('local')->exists($this->logFile)) {
            $addedFiles = json_decode(Storage::disk('local')->get($this->logFile), true);

            // Delete the logged files
            Storage::disk('local')->delete($addedFiles);

            // Remove the log file itself
            Storage::disk('local')->delete($this->logFile);
        }

        if (!empty($addedFiles)) {
            // Bulk delete the associated files from the database
            $resortImageIds = ResortImage::whereIn('image', $addedFiles)->pluck('id');
            DB::table('system_files')->whereIn('attachment_id', $resortImageIds)->delete();

            // Truncate the ResortImage table entries that were seeded
            ResortImage::whereIn('image', $addedFiles)->delete();
        }
    }
}
