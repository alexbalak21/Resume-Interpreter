<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentType::orderBy('name', 'asc')->get();
        return view('templates.index', compact('templates'));
    }

    public function install()
    {
        $installed = 0;
        $errors    = [];

        $basePath = storage_path('app/templates');

        if (! is_dir($basePath)) {
            return redirect()->route('templates.index')
                ->with('success', '0 template(s) installed.')
                ->with('errors_list', ["Folder not found: $basePath"]);
        }

        $folders = array_filter(glob($basePath . '/*'), 'is_dir');

        foreach ($folders as $folder) {
            $manifestPath = $folder . '/manifest.json';

            if (! file_exists($manifestPath)) {
                $errors[] = basename($folder) . ': manifest.json missing';
                continue;
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = basename($folder) . ': manifest.json is invalid JSON';
                continue;
            }

            $slug    = $manifest['slug'];
            $version = $manifest['version'] ?? '1.0';

            // Skip if already installed
            $exists = DB::table('document_types')
                ->where('slug', '=', $slug)
                ->where('version', '=', $version)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('document_types')->insert([
                'name'          => $manifest['name'],
                'slug'          => $slug,
                'description'   => $manifest['description'] ?? null,
                'version'       => $version,
                'template_path' => $folder . '/' . $manifest['template'],
                'config_path'   => $folder . '/' . $manifest['form'],
                'preview_image' => isset($manifest['preview'])
                    ? $folder . '/' . $manifest['preview']
                    : null,
                'active'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $installed++;
        }

        return redirect()->route('templates.index')
            ->with('success', "$installed template(s) installed.")
            ->with('errors_list', $errors);
    }
}