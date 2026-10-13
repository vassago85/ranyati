<?php

namespace Database\Seeders;

use App\Support\Motivations\Templates\OnePageSelfDefenceTemplate;
use Illuminate\Database\Seeder;

class OnePageMotivationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        OnePageSelfDefenceTemplate::seed();
    }
}
