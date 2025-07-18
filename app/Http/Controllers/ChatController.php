<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /**
     * Process blockchain query and return AI response
     */
    public function processBlockchainQuery(Request $request)
    {
        $query = $request->input('query');
        $sessionId = $request->input('session_id', 'default');

        try {
            // Analyze query for blockchain-specific context
            $context = $this->analyzeBlockchainQuery($query);
            
            // Get relevant blockchain data based on context
            $blockchainData = $this->getRelevantBlockchainData($context);
            
            // Create enhanced prompt with blockchain context
            $enhancedPrompt = $this->createEnhancedPrompt($query, $context, $blockchainData);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'context' => $context,
                'blockchain_data' => $blockchainData,
                'enhanced_prompt' => $enhancedPrompt,
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            Log::error('Chat processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process query'
            ], 500);
        }
    }

    private function analyzeBlockchainQuery($query)
    {
        $query = strtolower($query);
        $context = [
            'type' => 'general',
            'network' => null,
            'intent' => null,
            'entities' => []
        ];

        // Detect blockchain networks
        if (preg_match('/\b(bitcoin|btc)\b/', $query)) {
            $context['network'] = 'bitcoin';
        } elseif (preg_match('/\b(ethereum|eth)\b/', $query)) {
            $context['network'] = 'ethereum';
        }

        // Detect query intent
        if (preg_match('/\b(transaction|tx|transfer)\b/', $query)) {
            $context['intent'] = 'transaction';
        } elseif (preg_match('/\b(block|blocks)\b/', $query)) {
            $context['intent'] = 'block';
        } elseif (preg_match('/\b(price|value|cost)\b/', $query)) {
            $context['intent'] = 'price';
        } elseif (preg_match('/\b(address|wallet)\b/', $query)) {
            $context['intent'] = 'address';
        }

        // Extract addresses or hashes
        if (preg_match('/\b0x[a-fA-F0-9]{40}\b/', $query, $matches)) {
            $context['entities']['eth_address'] = $matches[0];
        }
        if (preg_match('/\b0x[a-fA-F0-9]{64}\b/', $query, $matches)) {
            $context['entities']['eth_hash'] = $matches[0];
        }
        if (preg_match('/\b[13][a-km-zA-HJ-NP-Z1-9]{25,34}\b/', $query, $matches)) {
            $context['entities']['btc_address'] = $matches[0];
        }

        // Extract numbers (for block numbers, amounts, etc.)
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\b/', $query, $matches)) {
            $context['entities']['numbers'] = $matches[1];
        }

        // Geographic mentions
        if (preg_match('/\b(usa|america|us|europe|asia|china|japan)\b/', $query, $matches)) {
            $context['entities']['location'] = $matches[0];
        }

        return $context;
    }

    private function getRelevantBlockchainData($context)
    {
        $data = [];

        try {
            // Get current market data
            $marketData = Cache::remember('market_data', 300, function () {
                return $this->getMarketData();
            });
            $data['market'] = $marketData;

            // Get network-specific data
            if ($context['network'] === 'bitcoin') {
                $data['bitcoin'] = $this->getBitcoinCurrentData();
            } elseif ($context['network'] === 'ethereum') {
                $data['ethereum'] = $this->getEthereumCurrentData();
            } else {
                // Get both if network not specified
                $data['bitcoin'] = $this->getBitcoinCurrentData();
                $data['ethereum'] = $this->getEthereumCurrentData();
            }

            // Get specific entity data if addresses/hashes provided
            if (isset($context['entities']['eth_address'])) {
                $data['eth_address_data'] = $this->getEthereumAddressData($context['entities']['eth_address']);
            }
            if (isset($context['entities']['btc_address'])) {
                $data['btc_address_data'] = $this->getBitcoinAddressData($context['entities']['btc_address']);
            }

        } catch (\Exception $e) {
            Log::error('Blockchain data fetch error: ' . $e->getMessage());
            $data['error'] = 'Failed to fetch some blockchain data';
        }

        return $data;
    }

    private function createEnhancedPrompt($originalQuery, $context, $blockchainData)
    {
        $prompt = "You are Blockpeek AI, a friendly blockchain expert that explains complex crypto concepts in simple terms for everyone.\n\n";
        
        $prompt .= "User Query: {$originalQuery}\n\n";
        
        $prompt .= "Context Analysis:\n";
        $prompt .= "- Network: " . ($context['network'] ?? 'not specified') . "\n";
        $prompt .= "- Intent: " . ($context['intent'] ?? 'general inquiry') . "\n";
        
        if (!empty($context['entities'])) {
            $prompt .= "- Entities found: " . json_encode($context['entities']) . "\n";
        }
        
        $prompt .= "\nCurrent Blockchain Data:\n";
        
        if (isset($blockchainData['market'])) {
            $market = $blockchainData['market'];
            $prompt .= "- Bitcoin: $" . number_format($market['bitcoin']['price'] ?? 0, 2) . " (24h change: " . round($market['bitcoin']['change24h'] ?? 0, 2) . "%)\n";
            $prompt .= "- Ethereum: $" . number_format($market['ethereum']['price'] ?? 0, 2) . " (24h change: " . round($market['ethereum']['change24h'] ?? 0, 2) . "%)\n";
        }
        
        if (isset($blockchainData['bitcoin'])) {
            $btc = $blockchainData['bitcoin'];
            $prompt .= "- Bitcoin latest block: " . ($btc['latestBlock'] ?? 'unknown') . "\n";
        }
        
        if (isset($blockchainData['ethereum'])) {
            $eth = $blockchainData['ethereum'];
            $prompt .= "- Ethereum latest block: " . ($eth['latestBlock'] ?? 'unknown') . "\n";
        }
        
        $prompt .= "\nPlease provide a helpful, accurate response that:\n";
        $prompt .= "1. Answers the user's question using the provided blockchain data\n";
        $prompt .= "2. Explains complex concepts in simple terms\n";
        $prompt .= "3. Includes relevant current data and context\n";
        $prompt .= "4. Is friendly and accessible to non-crypto users\n";
        $prompt .= "5. Suggests follow-up questions or actions if appropriate\n";
        
        return $prompt;
    }

    private function getMarketData()
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum',
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
                'include_24hr_vol' => 'true'
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch market data'];
        }
    }

    private function getBitcoinCurrentData()
    {
        try {
            $latestBlock = Http::get('https://blockchain.info/latestblock');
            return $latestBlock->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch Bitcoin data'];
        }
    }

    private function getEthereumCurrentData()
    {
        try {
            $response = Http::get('https://api.etherscan.io/api', [
                'module' => 'proxy',
                'action' => 'eth_blockNumber',
                'apikey' => env('ETHERSCAN_API_KEY')
            ]);

            return ['latestBlock' => hexdec($response->json()['result'] ?? '0x0')];
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch Ethereum data'];
        }
    }

    private function getEthereumAddressData($address)
    {
        try {
            $response = Http::get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'balance',
                'address' => $address,
                'tag' => 'latest',
                'apikey' => env('ETHERSCAN_API_KEY')
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch Ethereum address data'];
        }
    }

    private function getBitcoinAddressData($address)
    {
        try {
            $response = Http::get("https://blockchain.info/rawaddr/{$address}?format=json&limit=1");
            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch Bitcoin address data'];
        }
    }
}