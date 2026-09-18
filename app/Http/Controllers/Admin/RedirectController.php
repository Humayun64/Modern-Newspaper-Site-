<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RedirectController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500'],
            'to_path'   => ['required', 'string', 'max:500'],
            'status'    => ['required', 'in:301,302'],
        ]);

        $from = Redirect::normalise($data['from_path']);
        $to   = Redirect::normalise($data['to_path']);

        if ($from === '') {
            throw ValidationException::withMessages([
                'from_path' => 'The old address cannot be the homepage.',
            ]);
        }

        if ($from === $to) {
            throw ValidationException::withMessages([
                'to_path' => 'That would redirect an address to itself.',
            ]);
        }

        Redirect::updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status' => (int) $data['status'], 'source' => 'manual']
        );

        return back()->with('redirect_saved', true);
    }

    public function destroy(Request $request, Redirect $redirect)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $redirect->delete();

        return back()->with('redirect_saved', true);
    }
}
