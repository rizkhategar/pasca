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

    public function getJudulVisiAttribute(): ?string
    {
        return $this->vision_title;
    }

    public function getVisiAttribute(): mixed
    {
        return $this->vision;
    }

    public function getJudulMisiAttribute(): ?string
    {
        return $this->mission_title;
    }

    public function getMisiAttribute(): mixed
    {
        return $this->mission;
    }

    public function getJudulTujuanAttribute(): ?string
    {
        return $this->objectives_title;
    }

    public function getTujuanAttribute(): mixed
    {
        return $this->objectives;
    }

    public function getJudulTujuanBidangAttribute(): ?string
    {
        return $this->field_objectives_title;
    }

    public function getTujuanBidangAttribute(): mixed
    {
        return $this->field_objectives;
    }

    public function getJudulSasaranTargetAttribute(): ?string
    {
        return $this->goals_targets_title;
    }

    public function getSasaranTargetAttribute(): mixed
    {
        return $this->goals_targets;
    }
}
