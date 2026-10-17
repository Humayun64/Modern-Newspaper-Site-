<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalCoverage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GlobalCoverageController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'outlet'       => ['nullable', 'string', 'max:120'],
            'summary'      => ['nullable', 'string', 'max:400'],
            'source_url'   => ['nullable', 'url', 'max:500'],
            'published_on' => ['nullable', 'date'],
            // 12 MB: a scanned newspaper page is bigger than a web PDF.
            'pdf'          => ['nullable', 'file', 'mimetypes:application/pdf', 'max:12288'],
            'cover'        => ['nullable', 'image', 'max:2048'],
        ], [
            'pdf.mimetypes' => 'That file is not a PDF.',
        ]);

        $row = [
            'title'        => $data['title'],
            'outlet'       => $data['outlet'] ?? null,
            'summary'      => $data['summary'] ?? null,
            'source_url'   => $data['source_url'] ?? null,
            'published_on' => $data['published_on'] ?? null,
            'is_active'    => true,
            'sort_order'   => (int) GlobalCoverage::max('sort_order') + 1,
        ];

        if ($request->hasFile('pdf')) {
            $row['pdf_path'] = $request->file('pdf')->store('coverage', 'public');
        }

        if ($request->hasFile('cover')) {
            $row['cover_path'] = $request->file('cover')->store('coverage', 'public');
        }

        GlobalCoverage::create($row);

        Cache::forget('home.global_coverage');

        return back()->with('coverage_saved', 'Added.');
    }

    public function toggle(Request $request, GlobalCoverage $coverage)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $coverage->update(['is_active' => ! $coverage->is_active]);

        Cache::forget('home.global_coverage');

        return back()->with('coverage_saved', $coverage->is_active ? 'Shown.' : 'Hidden.');
    }

    public function destroy(Request $request, GlobalCoverage $coverage)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        // Only delete files we put on the disk, never a pasted URL.
        foreach ([$coverage->pdf_path, $coverage->cover_path] as $path) {
            if (filled($path) && ! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
                Storage::disk('public')->delete($path);
            }
        }

        $coverage->delete();

        Cache::forget('home.global_coverage');

        return back()->with('coverage_saved', 'Removed.');
    }
}
