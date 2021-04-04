<?php

namespace Underflip\Resorts\Tests;

use PluginTestCase;
use System\Classes\PluginBase;
use System\Classes\PluginManager;

/**
 * A test case that handle registration and boot of all required dependencies
 *
 * Based on example from https://octobercms.com/docs/help/unit-testing
 */
class BaseTestCase extends PluginTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Register all plugins
        PluginManager::instance()->registerAll(true);

        // Boot the plugins we need
        $this->getPluginObject('Nocio.Headstart')->boot();
        $this->getPluginObject()->boot();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Get the plugin manager
        PluginManager::instance()->unregisterAll();
    }
}
