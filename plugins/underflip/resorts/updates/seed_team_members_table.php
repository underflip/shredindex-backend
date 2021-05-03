<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Seeder;
use Underflip\Resorts\Models\Supporter;
use Underflip\Resorts\models\TeamMember;

class SeedTeamMembersTable extends Seeder
{
    public function run()
    {
        $tom = TeamMember::create([
            'name' => 'T Hansen',
            'url' => 'https://thomasandrewhansen.com',
        ]);

        $tom->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/teammembers/t-hansen.png',
        ]);

        $jd = TeamMember::create([
            'name' => 'jakxnz',
            'url' => 'https://github.com/jakxnz',
        ]);

        $jd->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/teammembers/jakxnz.png',
        ]);
    }
}
