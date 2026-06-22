<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisionMission extends Model
{
    use HasFactory;

    protected $table = 'vision_missions';

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'vision_title',
        'vision',
        'mission_title',
        'mission',
        'objectives_title',
        'objectives',
        'field_objectives_title',
        'field_objectives',
        'goals_targets_title',
        'goals_targets',
    ];

    protected $casts = [
        'vision' => 'array',
        'mission' => 'array',
        'objectives' => 'array',
        'field_objectives' => 'array',
        'goals_targets' => 'array',
    ];
}
