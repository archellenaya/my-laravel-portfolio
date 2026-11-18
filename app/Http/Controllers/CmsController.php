<?php

namespace App\Http\Controllers;

use App\Services\PortfolioPresentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    private function fields(string $section): array
    {
        abort_unless(array_key_exists($section, config('cms')), 404);

        return config("cms.$section");
    }

    public function index()
    {
        $counts = [];
        foreach (config('cms') as $table => $fields) {
            $counts[$table] = DB::table($table)->count();
        }

        return view('admin.index', compact('counts'));
    }

    public function listing(string $section)
    {
        $fields = $this->fields($section);
        $rows = DB::table($section)->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.list', compact('section', 'fields', 'rows'));
    }

    public function create(string $section)
    {
        $fields = $this->fields($section);
        abort_if($section === 'profiles', 404);

        return view('admin.edit', ['section' => $section, 'fields' => $fields, 'row' => null]);
    }

    public function edit(string $section, int $id)
    {
        $fields = $this->fields($section);
        $row = DB::table($section)->find($id);
        abort_unless($row, 404);

        return view('admin.edit', compact('section', 'fields', 'row'));
    }

    public function save(Request $request, string $section, ?int $id = null)
    {
        $fields = $this->fields($section);
        abort_if($section === 'profiles' && ! $id, 403);
        $existing = $id ? DB::table($section)->find($id) : null;
        if ($id) {
            abort_unless($existing, 404);
        }
        $rules = ['sort_order' => 'required|integer|min:0|max:100000'];
        foreach ($fields as $field => $type) {
            if ($type === 'approval') {
                $rules[$field] = ['nullable', 'boolean'];

                continue;
            }
            if (in_array($field, ['headshot', 'photo', 'photo_retina', 'image', 'resume'])) {
                $rules[$field.'_upload'] = [empty($existing?->$field) ? 'required' : 'nullable', 'file', $field === 'resume' ? 'mimes:pdf' : 'image', $field === 'resume' ? 'max:10240' : 'max:8192'];
                if ($field !== 'resume') {
                    $rules[$field.'_upload'][] = 'mimes:jpg,jpeg,png,webp,gif';
                }

                continue;
            }

            if ($section === 'tools' && $field === 'appearance') {
                $rules[$field] = ['required', 'in:original,light'];

                continue;
            }
            if ($section === 'projects' && $field === 'website_url') {
                $rules[$field] = ['nullable', 'string', 'max:1000', 'url:http,https'];

                continue;
            }

            $presence = str_starts_with($type, 'optional_') ? 'nullable' : 'required';
            $rules[$field] = [$presence, 'string', str_ends_with($type, 'textarea') ? 'max:20000' : 'max:1000'];
            if ($section === 'case_studies' && $field === 'description' && $request->boolean('approved')) {
                $rules[$field][0] = 'required';
            }
            if ($field === 'screenshot_path') {
                $rules[$field][] = function ($attribute, $path, $fail) {
                    if (! is_string($path) || ! PortfolioPresentation::publicScreenshot($path)) {
                        $fail('Choose an existing public image path under images/.');
                    }
                };
                $rules[$field][] = 'required_with:screenshot_alt';
            }
            if ($field === 'screenshot_alt') {
                $rules[$field][] = 'required_with:screenshot_path';
            }
            if ($field === 'email') {
                $rules[$field][] = 'email';
            }
            if (str_ends_with($field, '_url')) {
                $rules[$field][] = 'url:http,https';
            }
            if ($field === 'whatsapp') {
                $rules[$field][] = 'regex:/^[0-9]{7,15}$/';
            }
            if ($field === 'contact_number') {
                $rules[$field][] = 'regex:/^\+?[0-9 ()-]{7,25}$/';
            }
        }
        $values = $request->validate($rules);
        if (array_key_exists('approved', $fields)) {
            $values['approved'] = (bool) ($values['approved'] ?? false);
        }
        if ($section === 'projects') {
            $values['website_url'] = $values['website_url'] ?? '';
        }
        $stored = [];
        foreach (['headshot', 'photo', 'photo_retina', 'image', 'resume'] as $field) {
            unset($values[$field.'_upload']);
        }
        try {
            foreach (array_keys($fields) as $field) {
                if (in_array($field, ['headshot', 'photo', 'photo_retina', 'image', 'resume']) && $request->hasFile($field.'_upload')) {
                    $path = $request->file($field.'_upload')->store('cms', 'public');
                    if (! $path) {
                        throw new \RuntimeException('Unable to save uploaded file.');
                    }
                    $stored[] = $path;
                    $values[$field] = 'storage/'.$path;
                }
            }
            $values['updated_at'] = now();
            if ($id) {
                DB::table($section)->where('id', $id)->update($values);
            } else {
                DB::table($section)->insert($values + ['created_at' => now()]);
            }

        } catch (\Throwable $error) {
            Storage::disk('public')->delete($stored);
            throw $error;
        }

        $status = match ($section) {
            'case_studies' => $values['approved']
                ? 'Case study approved. Use Publish Static to include it in the next static build.'
                : 'Draft saved. The case study is excluded from new public builds.',
            'projects' => 'Project saved. Use Publish Static to refresh the static gallery.',
            default => 'Content saved. Your changes are live on the site.',
        };

        return redirect()->route('admin.list', $section)->with('status', $status);
    }

    public function destroy(string $section, int $id)
    {
        $this->fields($section);
        abort_if($section === 'profiles', 403);
        abort_unless(DB::table($section)->where('id', $id)->delete(), 404);

        return redirect()->route('admin.list', $section)->with('status', 'Content deleted.');
    }
}
