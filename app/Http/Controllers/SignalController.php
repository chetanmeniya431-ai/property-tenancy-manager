<?php

namespace App\Http\Controllers;

use App\Models\SignalEvent;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function index()
    {
        $events = SignalEvent::with(['signal', 'property', 'tenancy', 'request'])
            ->orderByDesc('triggered_at')
            ->get();

        return view('signals.index', [
            'open' => $events->whereNull('resolved_at'),
            'resolved' => $events->whereNotNull('resolved_at')->take(20),
        ]);
    }

    public function resolve(Request $request, SignalEvent $signalEvent)
    {
        $signalEvent->forceFill([
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ])->save();

        return back()->with('status', 'Signal resolved.');
    }
}
