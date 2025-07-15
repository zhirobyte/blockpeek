<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class TrackerController extends Controller
{
   

public function getEthereumTransactions()
{
    $nodePath = env('NODE_PATH', 'node'); // or full path to node.exe
    $scriptPath = base_path('resources/js/tracker.js');
    $process = new Process([$nodePath, $scriptPath]);
    $process->run();

    $raw = $process->getOutput();
    Log::info("Tracker Raw Output: " . $raw); // 🔍 DEBUG

    if (!$process->isSuccessful()) {
        return view('blockpeek', [
            'txs' => [],
            'error' => 'Failed to run tracker script',
            'raw' => $process->getErrorOutput(),
        ]);
    }

    // Try to extract array from raw
    preg_match('/\[(.*?)\]/s', $raw, $matches);
    $parsed = isset($matches[0]) ? json_decode($matches[0], true) : [];

    return view('blockpeek', [
        'txs' => is_array($parsed) ? $parsed : [],
        'error' => null,
        'raw' => $raw,
    ]);



    
}

}