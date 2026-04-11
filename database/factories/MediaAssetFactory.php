<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory media asset untuk mensimulasikan upload project.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $project = Project::first() ?? Project::factory()->create();
        $user = User::first() ?? User::factory()->create();

        return [
            'project_id' => $project->id,
            'type' => 'RAW',
            'path' => "projects/{$project->id}/RAW/file.jpg",
            'uploaded_by' => $user->id,
            'version' => 1,
        ];
    }
}
