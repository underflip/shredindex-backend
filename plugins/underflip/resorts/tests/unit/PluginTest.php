<?php

namespace Underflip\Resorts\Tests;

use Underflip\Resorts\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use System\Classes\PluginManager;
use Illuminate\Contracts\Container\Container;
use System\Classes\SettingsManager;
use Backend;
use Config;
use Event;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Settings;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;
use Lang;

class PluginTest extends BaseTestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;
    protected Plugin $plugin;

    public function setUp(): void
    {
        parent::setUp();
        $mockApp = $this->createMock(Container::class);
        $this->plugin = new Plugin($mockApp);
        $this->plugin->registerComponents();
        $this->plugin->registerSettings();
    }

    public function testPluginDetails()
    {
        $details = $this->plugin->pluginDetails(); // Use the initialized plugin
        $this->assertEquals('Resorts', $details['name']);
        $this->assertEquals('Provides the Resorts part of Shred Index', $details['description']);
        $this->assertEquals('Underflip', $details['author']);
        $this->assertEquals('icon-icon-snowflake-o', $details['icon']);
    }

    public function testRegisterSettings()
    {
        $details = $this->plugin->registerSettings(); 
        $this->assertEquals('underflip.resorts::lang.settings.label', $details['settings']['label']);
#        $this->assertEquals('Shredindex Settings', $details['settings']['label']);
#        $this->assertEquals('Manage global settings', $details['settings']['description']);
    }

    public function testRegisterNavigation()
    {
        $navigation = $this->plugin->registerNavigation(); // Use the initialized plugin
        $this->assertArrayHasKey('resorts', $navigation);
        $this->assertEquals('Resorts', $navigation['resorts']['label']);
        $this->assertEquals(Backend::url('underflip/resorts/resorts'), $navigation['resorts']['url']);
        $this->assertEquals('icon-wrench', $navigation['resorts']['icon']);
        $this->assertArrayHasKey('resorts', $navigation['resorts']['sideMenu']);
        $this->assertArrayHasKey('types', $navigation['resorts']['sideMenu']);
    }


    public function testRegisterListColumnTypes()
    {
        $columnTypes = $this->plugin->registerListColumnTypes(); // Use the initialized plugin
        $this->assertArrayHasKey('unit', $columnTypes);
        $this->assertArrayHasKey('shortname', $columnTypes);

        // Test unit column type
        $this->assertEquals('None', $columnTypes['unit'](null));
        $this->assertEquals('score', $columnTypes['unit']('score'));

        // Test shortname column type
        $this->assertEquals('', $columnTypes['shortname']('NonExistentClass'));
        $this->assertEquals('Rating', $columnTypes['shortname'](Rating::class));
    }

    public function testResortModelExists()
    {
        $this->assertTrue(class_exists(\Underflip\Resorts\Models\Resort::class));
    }

    public function testLang()
    {
        $this->assertEquals('Resorts',Lang::get('underflip.resorts::lang.plugin.name'));
    }

}
