<?php namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Tests\Support\Project1Model;

class UuidSeeder extends Seeder
{
	public function run()
	{
		// Project 1
		$inserts = [
			[
				'id'           => '¸¨/âÿ"M.¶Ô!Yx4',
				'name'         => 'Name 1',
				'description'  => 'Description 1',
				'created_at'   => date('Y-m-d H:i:s', strtotime('now')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('now')),
			],
			[
				'id'           => '$%êwtMªTáÿ4ªÊÉ',
				'name'         => 'Name 2',
				'description'  => 'Description 2',
				'created_at'   => date('Y-m-d H:i:s', strtotime('+1 minute')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('+1 minute')),
			],
			[
				'id'           => 'Ê¹ÄØDµ§°ïÞ®h',
				'name'         => 'Name 3',
				'description'  => 'Description 3',
				'created_at'   => date('Y-m-d H:i:s', strtotime('+2 minutes')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('+2 minutes')),
			],
        ];

		$builder = $this->db->table('projects1');
		
		foreach ($inserts as $insert)
		{
			$builder->insert($insert);
		}

		// Project 2
		$inserts = [
			[
				'category_id'  => '¸¨/âÿ"M.¶Ô!Yx4',
				'name'         => 'Name 1',
				'description'  => 'Description 1',
				'created_at'   => date('Y-m-d H:i:s', strtotime('now')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('now')),
			],
			[
				'category_id'  => '$%êwtMªTáÿ4ªÊÉ',
				'name'         => 'Name 2',
				'description'  => 'Description 2',
				'created_at'   => date('Y-m-d H:i:s', strtotime('+1 minute')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('+1 minute')),
			],
			[
				'category_id'  => 'Ê¹ÄØDµ§°ïÞ®h',
				'name'         => 'Name 3',
				'description'  => 'Description 3',
				'created_at'   => date('Y-m-d H:i:s', strtotime('+2 minutes')),
				'updated_at'   => date('Y-m-d H:i:s', strtotime('+2 minutes')),
			],
        ];

		$builder = $this->db->table('projects2');
		
		foreach ($inserts as $insert)
		{
			$builder->insert($insert);
		}
	}
}
