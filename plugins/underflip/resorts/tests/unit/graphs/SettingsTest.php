<?php

namespace Underflip\Resorts\Tests\Graphs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use System\Behaviors\SettingsModel;
use Underflip\Resorts\Models\Settings;
use Underflip\Resorts\Models\Supporter;
use Underflip\Resorts\models\TeamMember;
use Underflip\Resorts\Tests\BaseTestCase;

/**
 * Test the GraphQL Types declared in /graphql/headstart/graphs/settings.htm
 */
class SettingsTest extends BaseTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Settings::clearInternalCache();

        Supporter::truncate();
        TeamMember::truncate();
    }

    public function testSettings()
    {
        $copyrightMessage = '[Foo](https://foo.com} bar *baz*';

        Settings::set(
            'copyright_message',
            $copyrightMessage
        );

        $response = $this->graphQL('
            {
                 settings {
                    copyright_message
                }
            }
        ');

        $this->assertSame(
            $copyrightMessage,
            $response->json('data.settings.copyright_message'),
            'Should graph copyright message'
        );
    }

    public function testSupporters()
    {
        Model::unguard();

        $supporter = Supporter::create([
            'name' => 'Foo',
            'url' => 'https://www.foo.com/bar',
            'sort_order' => 1,
        ]);

        $supporter->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/tests/fixtures/assets/foo-bar-baz.jpeg',
            ['file_name' => 'foo-bar-baz.jpeg', 'content_type' => 'image/jpeg','title' => 'Foo Bar Baz',
                'description' => 'foo.bar = baz;' ]
        );

        Model::reguard();

        $response = $this->graphQL('
            {
                 supporters {
                    name
                    url
                    sort_order
                    image {
                        disk_name
                        content_type
                        file_size
                        title
                        description
                        path
                    }
                }
            }
        ');

        $this->assertSame(
            $response->json('data.supporters.0.name'),
            'Foo',
            'Should have a name'
        );

        $this->assertSame(
            $response->json('data.supporters.0.url'),
            'https://www.foo.com/bar',
            'Should have a name'
        );

        $this->assertSame(
            $response->json('data.supporters.0.sort_order'),
            1,
            'Should have a name'
        );

        $this->assertStringEndsWith(
            '.jpeg',
            $response->json('data.supporters.0.image.disk_name'),
            'Should have disk name'
        );

        $this->assertSame(
            $response->json('data.supporters.0.image.content_type'),
            'image/jpeg',
            'Should have content type'
        );

        $this->assertSame(
            $response->json('data.supporters.0.image.file_size'),
            12018,
            'Should have file size'
        );

        $this->assertSame(
            $response->json('data.supporters.0.image.title'),
            'Foo Bar Baz',
            'Should have title'
        );

        $this->assertSame(
            $response->json('data.supporters.0.image.description'),
            'foo.bar = baz;',
            'Should have description'
        );

        $this->assertSame(
            $response->json('data.supporters.0.image.description'),
            'foo.bar = baz;',
            'Should have description'
        );

         $this->assertRegExp(
             '/^(http|https):\/\/([\d\w.]+|localhost:\d{2,5})\/storage\/app\/uploads\/public\/[\d\w\/]+\/[\d\w]+.jpeg$/',
             $response->json('data.supporters.0.image.path'),
             'Should have path'
         );
    }

    public function testTeamMembers()
    {
        Model::unguard();

        $member = TeamMember::create([
            'name' => 'Bar',
            'url' => 'https://www.bar.com/baz',
            'sort_order' => 1,
        ]);

        $member->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/tests/fixtures/assets/foo-bar-baz.jpeg',
            ['file_name' => 'foo-bar-baz.jpeg', 'content_type' => 'image/jpeg','title' => 'Foo Bar Baz',
                'description' => 'foo.bar = baz;' ]
        );

        Model::reguard();

        $response = $this->graphQL('
            {
                 teamMembers {
                    name
                    url
                    sort_order
                    image {
                        disk_name
                        content_type
                        file_size
                        title
                        description
                        path
                    }
                }
            }
        ');

        $this->assertSame(
            $response->json('data.teamMembers.0.name'),
            'Bar',
            'Should have a name'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.url'),
            'https://www.bar.com/baz',
            'Should have a name'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.sort_order'),
            1,
            'Should have a name'
        );

        $this->assertStringEndsWith(
            '.jpeg',
            $response->json('data.teamMembers.0.image.disk_name'),
            'Should have disk name'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.image.content_type'),
            'image/jpeg',
            'Should have content type'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.image.file_size'),
            12018,
            'Should have file size'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.image.title'),
            'Foo Bar Baz',
            'Should have title'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.image.description'),
            'foo.bar = baz;',
            'Should have description'
        );

        $this->assertSame(
            $response->json('data.teamMembers.0.image.description'),
            'foo.bar = baz;',
            'Should have description'
        );

        $this->assertRegExp(
            '/^(http|https):\/\/([\d\w.]+|localhost:\d{2,5})\/storage\/app\/uploads\/public\/[\d\w\/]+\/[\d\w]+.jpeg$/',
            $response->json('data.teamMembers.0.image.path'),
            'Should have path'
        );
    }
}
