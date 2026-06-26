<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalStructure;
use Illuminate\Http\Response;

class OrganizationStructureController extends Controller
{
    public function index(): Response
    {
        $organizationStructure = OrganizationalStructure::query()
            ->where('is_active', true)
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (! $organizationStructure) {
            $organizationStructure = OrganizationalStructure::query()
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->latest('updated_at')
                ->latest('id')
                ->first();
        }

        $strukturOrganisasi = $organizationStructure;

        return response()
            ->view('profile.organization-structure', compact('strukturOrganisasi', 'organizationStructure'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
