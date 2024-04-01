<?php namespace System\Classes;

use Url;
use Log;
use App;
use File;
use Lang;
use Route;
use Event;
use Cache;
use Config;
use Storage;
use Redirect;
use Exception;
use ApplicationException;
use Resizer;

/**
 * ResizeImages is used for resizing image files
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class ResizeImages
{
    /**
     * @var array availableSources to get image paths
     */
    protected $availableSources = [];

    /**
     * @var string storageFolder is the name of the folder in the resources disk
     */
    protected $storageFolder = 'resize';

    /**
     * @var string storageUrl relative or absolute URL of the Library root folder.
     */
    protected $storageUrl;

    /**
     * __construct this instance
     */
    public function __construct()
    {
        $this->storageUrl = rtrim(Config::get('filesystems.disks.resources.url', '/storage/app/resources'), '/');
    }

    /**
     * instance creates a new instance of this singleton
     */
    public static function instance(): static
    {
        return App::make('system.resizer');
    }

    /**
     * resize generates and returns a thumbnail URL path
     *
     * @param integer $width
     * @param integer $height
     * @param array $options [
     *     'mode' => 'auto',
     *     'offset' => [0, 0],
     *     'quality' => 90,
     *     'sharpen' => 0,
     *     'interlace' => false,
     *     'extension' => 'auto',
     * ]
     * @return string
     */
    public static function resize($image, $width = null, $height = null, $options = [])
    {
        return self::instance()->prepareRequest($image, $width, $height, $options);
    }

    /**
     * prepareRequest checks if an image has been resized before and returns the URL,
     * otherwise the performs the resize by passing to a route that ends up at getContents
     */
    protected function prepareRequest($image, $width = null, $height = null, $options = [])
    {
        $imageItem = (new ResizeImageItem)->fromObject($image);
        $imageItem->toOptions($options);
        $imageItem->toDimensions($width, $height);

        if (!$imageItem->isResizable()) {
            return $imageItem->url;
        }

        // Check is resized
        if ($this->hasFile($imageItem)) {
            return $this->getPublicPath($imageItem);
        }

        // Cache and process
        $cacheKey = $imageItem->getCacheKey();
        $cacheInfo = $this->getCache($cacheKey);

        if (!$cacheInfo) {
            $this->putCache($cacheKey, $imageItem->getCacheInfo());
        }

        $outputFilename = $imageItem->getCacheVersion();

        return $this->getResizedUrl($outputFilename);
    }

    /**
     * getContents performs the resize and stores in on disk, creating a cache
     */
    public function getContents($cacheKey)
    {
        $cacheInfo = $this->getCache($cacheKey);
        if (!$cacheInfo || !isset($cacheInfo['path'])) {
            throw new ApplicationException(__("The resizer file ':name' is not found.", ['name' => e($cacheKey)]));
        }

        $imageItem = (new ResizeImageItem)->fromCacheInfo($cacheKey, $cacheInfo);

        // Set local paths for resizer
        $tempFilename = $imageItem->getPartitionDirectory() . '_' . $imageItem->getFilename();
        $tempTargetPath = $this->getTempPath() . '/' . $tempFilename;
        $tempSourcePath = $this->getTempPath() . '/raw_' . $tempFilename;
        $sourcePath = $this->getSourcePathForResize($cacheInfo['path'], $tempSourcePath);

        // Perform resize
        Resizer::open($sourcePath)
            ->resize($imageItem->width, $imageItem->height, $imageItem->options)
            ->save($tempTargetPath);

        // Save resized file to disk
        $disk = Storage::disk('resources');
        $filePath = $this->getStoragePath($imageItem);
        $success = $disk->putFileAs(
            dirname($filePath),
            $tempTargetPath,
            basename($filePath)
        );

        // Clean up
        File::delete($tempTargetPath);

        if (file_exists($tempSourcePath)) {
            File::delete($tempSourcePath);
        }

        // Eagerly cache remote exists call
        if ($success && !$this->isLocalStorage()) {
            Cache::forever($this->getExistsCacheKey($filePath), true);
        }

        return Redirect::to($this->getPublicPath($imageItem));
    }

    /**
     * getSourcePathForResize creates a temp copy of external files in the local filesystem
     */
    protected function getSourcePathForResize($realSourcePath, $tempSourcePath)
    {
        $isExternal = strpos($realSourcePath, 'http') === 0;
        $sourcePath = $isExternal ? $tempSourcePath : $realSourcePath;

        if ($isExternal) {
            try {
                $contents = file_get_contents($realSourcePath);
                file_put_contents($tempSourcePath, $contents);
            }
            catch (Exception $ex) {
                Log::warning('Unable to fetch external image ' . $realSourcePath . ' ['.$ex->getMessage().']');
            }
        }

        if (!file_exists($sourcePath)) {
            /**
             * @event system.resizer.handleMissingImage
             * Provides an opportunity to configure a custom image when the resizer couldn't find the original file
             *
             * Example usage:
             *
             *     Event::listen('system.resizer.handleMissingImage', function(&$sourcePath) {
             *         $sourcePath = plugins_path('vendor/plugin/assets/broken-image.jpg');
             *     });
             *
             */
            Event::fire('system.resizer.handleMissingImage', [&$sourcePath]);
        }

        return $sourcePath;
    }

    /**
     * hasFile checks file exists on storage device
     */
    protected function hasFile($imageItem): bool
    {
        $filePath = $this->getStoragePath($imageItem);

        $disk = Storage::disk('resources');
        if ($this->isLocalStorage()) {
            return $disk->exists($filePath);
        }

        // Cache remote storage results for performance increase
        $result = Cache::rememberForever($this->getExistsCacheKey($filePath), function() use ($disk, $filePath) {
            return $disk->exists($filePath);
        });

        return $result;
    }

    /**
     * getResizedUrl
     */
    protected function getResizedUrl($outputFilename = 'undefined.css')
    {
        $combineAction = \System\Classes\SystemController::class.'@resize';
        $actionExists = Route::getRoutes()->getByAction($combineAction) !== null;

        if ($actionExists) {
            $result = Url::action($combineAction, [$outputFilename], false);
        }
        else {
            $result = '/resize/'.$outputFilename;
        }

        return Url::toRelative($result);
    }

    //
    // Paths
    //

    /**
     * getStoragePath returns a relative storage path for the image
     */
    public function getStoragePath($imageItem)
    {
        return $this->storageFolder . '/' . $imageItem->getFilepath();
    }

    /**
     * getPublicPath returns the public address for the resources path
     */
    public function getPublicPath($imageItem)
    {
        $publicPath = $this->storageUrl . '/resize';

        if ($this->isLocalStorage() && Config::get('system.relative_links') === true) {
            $result = Url::toRelative($publicPath);
        }
        else {
            $result = Url::asset($publicPath);
        }

        return $result . '/' . $imageItem->getFilepath();
    }

    /**
     * getTempPath returns an internal working path
     */
    public function getTempPath()
    {
        $path = temp_path('resize');

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * isLocalStorage returns true if the storage engine is local
     */
    protected function isLocalStorage()
    {
        return Config::get('filesystems.disks.resources.driver') === 'local';
    }

    //
    // Cache
    //

    /**
     * putCache stores information about a asset collection against
     * a cache identifier.
     * @param string $cacheKey Cache identifier.
     * @param array $cacheInfo List of asset files.
     * @return bool Successful
     */
    protected function putCache($cacheKey, array $cacheInfo)
    {
        $cacheKey = 'resizer.'.$cacheKey;

        if (Cache::has($cacheKey)) {
            return false;
        }

        $this->putCacheIndex($cacheKey);

        Cache::forever($cacheKey, base64_encode(serialize($cacheInfo)));

        return true;
    }

    /**
     * getCache looks up information about a cache identifier.
     * @param string $cacheKey Cache identifier
     * @return array Cache information
     */
    protected function getCache($cacheKey)
    {
        $cacheKey = 'resizer.'.$cacheKey;

        if (!Cache::has($cacheKey)) {
            return false;
        }

        return @unserialize(@base64_decode(Cache::get($cacheKey)));
    }

    /**
     * getExistsCacheKey builds a key for caching the exists check
     */
    protected function getExistsCacheKey(string $filePath): string
    {
        $payload = ['type' => 'resizer-file', 'path' => $filePath];
        return md5(json_encode($payload));
    }

    /**
     * resetCache resets the resizer cache
     * @return void
     */
    public static function resetCache()
    {
        if (Cache::has('resizer.index')) {
            $index = (array) @unserialize(@base64_decode(Cache::get('resizer.index'))) ?: [];

            foreach ($index as $cacheKey) {
                Cache::forget($cacheKey);
            }

            Cache::forget('resizer.index');
        }

        // CacheHelper::instance()->clearCombiner();
    }

    /**
     * putCacheIndex adds a cache identifier to the index store used for
     * performing a reset of the cache.
     * @param string $cacheKey Cache identifier
     * @return bool Returns false if identifier is already in store
     */
    protected function putCacheIndex($cacheKey)
    {
        $index = [];

        if (Cache::has('resizer.index')) {
            $index = (array) @unserialize(@base64_decode(Cache::get('resizer.index'))) ?: [];
        }

        if (in_array($cacheKey, $index)) {
            return false;
        }

        $index[] = $cacheKey;

        Cache::forever('resizer.index', base64_encode(serialize($index)));

        return true;
    }
}
