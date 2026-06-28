<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SetupGuideController extends Controller
{
    public function index(Request $request)
    {
        $tracks = (array) config('setup_guides.tracks', []);

        if (empty($tracks)) {
            abort(404);
        }

        $trackKeys = array_keys($tracks);
        $selectedTrackKey = (string) $request->query('track', $trackKeys[0]);

        if (!array_key_exists($selectedTrackKey, $tracks)) {
            $selectedTrackKey = $trackKeys[0];
        }

        $selectedTrack = $tracks[$selectedTrackKey];

        return view('setup-guide.index', [
            'tracks' => $tracks,
            'selectedTrackKey' => $selectedTrackKey,
            'selectedTrack' => $selectedTrack,
        ]);
    }
}
