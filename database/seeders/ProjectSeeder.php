<?php

namespace Database\Seeders;

use App\Models\Project;
use Faker\Generator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(Generator $faker): void
  {
    for ($i = 0; $i < 50; $i++) {
      $project = new Project();
      $project->title = $faker->jobTitle();
      $project->url = $faker->url;
      $project->description = $faker->paragraphs(5, true);;
      $project->save();
    }
  }
}
