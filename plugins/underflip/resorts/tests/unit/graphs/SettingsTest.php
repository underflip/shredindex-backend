<?php

namespace Underflip\Resorts\Tests\Graphs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
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

        Supporter::truncate();
        TeamMember::truncate();
    }

    public function testSettings()
    {
        $this->markTestSkipped('Unable to reference Settings until answer comes from Core Maintainers');

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

        $supporter->image()->create([
            'title' => 'Foo Bar Baz',
            'description' => 'foo.bar = baz;',
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/tests/fixtures/assets/foo-bar-baz.jpeg',
        ]);

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
            '/^(http|https):\/\/[\d\w.]+\/storage\/app\/uploads\/public\/[\d\w\/]+\/[\d\w]+.jpeg$/',
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

        $member->image()->create([
            'title' => 'Foo Bar Baz',
            'description' => 'foo.bar = baz;',
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/tests/fixtures/assets/foo-bar-baz.jpeg',
        ]);

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
            '/^(http|https):\/\/[\d\w.]+\/storage\/app\/uploads\/public\/[\d\w\/]+\/[\d\w]+.jpeg$/',
            $response->json('data.teamMembers.0.image.path'),
            'Should have path'
        );
    }
}
