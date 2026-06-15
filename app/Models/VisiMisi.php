<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisiMisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'judul_visi',
        'visi',
        'judul_misi',
        'misi',
        'judul_tujuan',
        'tujuan',
        'judul_tujuan_bidang',
        'tujuan_bidang',
        'judul_sasaran_target',
        'sasaran_target',
    ];

    protected $casts = [
        'visi' => 'array',
        'misi' => 'array',
        'tujuan' => 'array',
        'tujuan_bidang' => 'array',
        'sasaran_target' => 'array',
    ];
}
