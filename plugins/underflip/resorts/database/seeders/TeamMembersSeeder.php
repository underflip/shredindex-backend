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

        $tom->image()->create([
            'disk_name' => 'local',
            'attachment_id' => $tom->id, // ID dari instance TeamMember yang baru dibuat
            'attachment_type' => TeamMember::class, // Nama kelas dari model yang ditautkan
            'is_public' => true, // Sesuaikan dengan kebutuhan Anda
            'file_name' => 't-hansen.png', // Nama file
            'file_size' => 12345, // Ukuran file dalam byte
            'content_type' => 'image/png', // Tipe konten file
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/teammembers/t-hansen.png',
        ]);

        $jd = TeamMember::create([
            'name' => 'jakxnz',
            'url' => 'https://github.com/jakxnz',
        ]);

        $jd->image()->create([
            'disk_name' => 'local',
            'attachment_id' => $jd->id, // ID dari instance TeamMember yang baru dibuat
            'attachment_type' => TeamMember::class, // Nama kelas dari model yang ditautkan
            'is_public' => true, // Sesuaikan dengan kebutuhan Anda
            'file_name' => 'jakxnz.png', // Nama file
            'file_size' => 12345, // Ukuran file dalam byte
            'content_type' => 'image/png', // Tipe konten file
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/teammembers/jakxnz.png',
        ]);
    }

    public function down()
    {
        foreach (TeamMember::all() as $teamMember) {
            $teamMember->image()->delete();
        }

        TeamMember::query()->truncate();
    }
}
