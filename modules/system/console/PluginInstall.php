<?php namespace System\Console;

use System;
use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use System\Helpers\Cache as CacheHelper;
use October\Rain\Composer\Manager as ComposerManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Exception;

/**
 * PluginInstall installs a new plugin.
 *
 * This adds a new plugin by requesting it from the October marketplace.
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginInstall extends Command
{
    /**
     * @var string name of console command
     */
    protected $name = 'plugin:install';

    /**
     * @var string description of the console command
     */
    protected $description = 'Install a plugin from the October marketplace or custom source.';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->line("Installing Plugin: {$name}");

        if ($src = $this->option('from')) {
            $this->info("Added Repo: {$src}");
            $composerCode = System::octoberToComposerCode(
                $name,
                'plugin',
                (bool) $this->option('oc')
            );
            $composerVersion = '*';

            $this->addRepoFromSource($composerCode, $src);
        }
        else {
            $info = UpdateManager::instance()->requestPluginDetails($name);
            $composerCode = array_get($info, 'composer_code');
            $composerVersion = '^'.array_get($info, 'composer_version');
        }

        // Splice in version
        if ($requireVersion = $this->option('want')) {
            $composerVersion = $requireVersion;
        }

        // Composer require
        $this->comment("Executing: composer require {$composerCode} {$composerVersion}");
        $this->line('');

        $composer = ComposerManager::instance();
        $composer->setOutputCommand($this, $this->input);

        try {
            if ($this->option('no-update')) {
                $composer->addPackages([$composerCode => $composerVersion]);
            }
            else {
                $composer->require([$composerCode => $composerVersion]);
            }
        }
        catch (Exception $ex) {
            if ($src = $this->option('from')) {
                $this->info("Reverted repo change");
                $this->removeRepoFromSource($composerCode);
            }
            throw $ex;
        }

        // Clear meta cache
        CacheHelper::instance()->clearMeta();

        // Run migrations
        if (!$this->option('no-migrate')) {
            $this->comment("Executing: php artisan october:migrate");
            $this->line('');

            // Migrate database
            $errCode = null;
            static::passthruArtisan('october:migrate', $errCode);
            $this->line('');

            if ($errCode !== 0) {
                $this->output->error('Migration failed. Check output above');
                exit(1);
            }
        }

        $this->output->success("Plugin '{$name}' installed");
    }

    /**
     * addRepoFromSource adds a plugin to composer's repositories
     */
    protected function addRepoFromSource($composerCode, $src)
    {
        if (file_exists(base_path($src))) {
            if (file_exists(base_path($src . '/.git'))) {
                $srcType = 'git';
            }
            else {
                $srcType = 'path';
            }
        }
        else {
            $srcType = 'git';
        }

        ComposerManager::instance()->addRepository($composerCode, $srcType, $src);
    }

    /**
     * removeRepoFromSource removes a plugin from composer's repo
     */
    protected function removeRepoFromSource($composerCode)
    {
        ComposerManager::instance()->removeRepository($composerCode);
    }

    /**
     * getArguments get the console command arguments
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the plugin. Eg: AuthorName.PluginName'],
        ];
    }

    /**
     * getOptions get the console command options
     */
    protected function getOptions()
    {
        return [
            ['oc', null, InputOption::VALUE_NONE, 'Package uses the oc- prefix.'],
            ['from', 'f', InputOption::VALUE_REQUIRED, 'Provide a custom source.'],
            ['want', 'w', InputOption::VALUE_REQUIRED, 'Provide a custom version.'],
            ['no-migrate', null, InputOption::VALUE_NONE, 'Do not run migration after install.'],
            ['no-update', null, InputOption::VALUE_NONE, 'Do not run composer update after install.'],
        ];
    }

    /**
     * passthruArtisan
     */
    protected static function passthruArtisan($command, &$errCode = null)
    {
        passthru('"'.PHP_BINARY.'" artisan ' .$command, $errCode);
    }
}
