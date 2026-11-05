<?php

namespace App\Http\Controllers;

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
            if (in_array($field, ['headshot', 'photo', 'photo_retina', 'image', 'resume'])) {
                $rules[$field.'_upload'] = [empty($existing?->$field) ? 'required' : 'nullable', 'file', $field === 'resume' ? 'mimes:pdf' : 'image', $field === 'resume' ? 'max:10240' : 'max:8192'];
                if ($field !== 'resume') {
                    $rules[$field.'_upload'][] = 'mimes:jpg,jpeg,png,webp,gif';
                }

                continue;
            }

            $rules[$field] = ['required', 'string', $type === 'textarea' ? 'max:20000' : 'max:1000'];
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

        return redirect()->route('admin.list', $section)->with('status', 'Content saved. Your changes are live on the site.');
    }

    public function destroy(string $section, int $id)
    {
        $this->fields($section);
        abort_if($section === 'profiles', 403);
        abort_unless(DB::table($section)->where('id', $id)->delete(), 404);

        return redirect()->route('admin.list', $section)->with('status', 'Content deleted.');
    }
}
