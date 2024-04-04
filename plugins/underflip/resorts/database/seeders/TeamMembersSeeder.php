<?php

namespace Underflip\Resorts\Database\Seeders;

use Seeder;
use Underflip\Resorts\models\TeamMember;

/**
 * @codeCoverageIgnore
 */
class TeamMembersSeeder extends Seeder implements Downable
{
    public function run()
    {
        $tom = TeamMember::create([
            'name' => 'T Hansen',
            'url' => 'https://thomasandrewhansen.com',
        ]);

        $tom->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/teammembers/t-hansen.png',
            ['file_name' => 't-hansen.png', 'content_type' => 'image/png']
        );


        $jd = TeamMember::create([
            'name' => 'jakxnz',
            'url' => 'https://github.com/jakxnz',
        ]);

        $jd->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/teammembers/jakxnz.png',
            ['file_name' => 'jakxnz.png', 'content_type' => 'image/png']
        );
    }

    public function down()
    {
        foreach (TeamMember::all() as $teamMember) {
            $teamMember->image()->delete();
        }

        TeamMember::query()->truncate();
    }
}
