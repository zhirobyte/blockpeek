<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BlockchainController extends Controller
{
    private $etherscanApiKey;
    
    public function __construct()
    {
        $this->etherscanApiKey = env('ETHERSCAN_API_KEY');
    }

    /**
     * Get real-time blockchain dashboard data
     */
    public function getDashboardData()
    {
        try {
            // Get cached data first for performance
            $cacheKey = 'blockchain_dashboard_' . now()->format('Y-m-d-H-i');
            
            return Cache::remember($cacheKey, 60, function () {
                $data = [
                    'ethereum' => $this->getEthereumData(),
                    'bitcoin' => $this->getBitcoinData(),
                    'market' => $this->getMarketData(),
                    'stats' => $this->getNetworkStats()
                ];
                
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'timestamp' => now()->toISOString()
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch dashboard data'
            ], 500);
        }
    }

    /**
     * Get Ethereum network data
     */
    private function getEthereumData()
    {
        try {
            // Get latest block
            $latestBlock = Http::get('https://api.etherscan.io/api', [
                'module' => 'proxy',
                'action' => 'eth_blockNumber',
                'apikey' => $this->etherscanApiKey
            ]);

            $blockNumber = hexdec($latestBlock->json()['result']);

            // Get block details
            $blockDetails = Http::get('https://api.etherscan.io/api', [
                'module' => 'proxy',
                'action' => 'eth_getBlockByNumber',
                'tag' => '0x' . dechex($blockNumber),
                'boolean' => 'true',
                'apikey' => $this->etherscanApiKey
            ]);

            $block = $blockDetails->json()['result'];

            // Get recent transactions
            $transactions = Http::get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'txlist',
                'address' => '0xde0b295669a9fd93d5f28d9ec85e40f4cb697bae',
                'startblock' => $blockNumber - 100,
                'endblock' => $blockNumber,
                'sort' => 'desc',
                'apikey' => $this->etherscanApiKey
            ]);

            return [
                'network' => 'Ethereum',
                'latestBlock' => $blockNumber,
                'blockHash' => $block['hash'] ?? '',
                'blockTime' => isset($block['timestamp']) ? date('Y-m-d H:i:s', hexdec($block['timestamp'])) : '',
                'transactions' => array_slice($transactions->json()['result'] ?? [], 0, 10),
                'gasPrice' => isset($block['gasUsed']) ? hexdec($block['gasUsed']) : 0,
                'difficulty' => isset($block['difficulty']) ? hexdec($block['difficulty']) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Ethereum data error: ' . $e->getMessage());
            return [
                'network' => 'Ethereum',
                'error' => 'Failed to fetch Ethereum data'
            ];
        }
    }

    /**
     * Get Bitcoin network data
     */
    private function getBitcoinData()
    {
        try {
            // Get latest stats
            $stats = Http::get('https://blockchain.info/stats?format=json');
            
            // Get latest blocks
            $latestBlocks = Http::get('https://blockchain.info/latestblock');
            
            // Get recent transactions
            $unconfirmedTxs = Http::get('https://blockchain.info/unconfirmed-transactions?format=json');

            return [
                'network' => 'Bitcoin',
                'latestBlock' => $latestBlocks->json()['height'] ?? 0,
                'blockHash' => $latestBlocks->json()['hash'] ?? '',
                'blockTime' => isset($latestBlocks->json()['time']) ? date('Y-m-d H:i:s', $latestBlocks->json()['time']) : '',
                'difficulty' => $stats->json()['difficulty'] ?? 0,
                'hashRate' => $stats->json()['hash_rate'] ?? 0,
                'totalBtc' => $stats->json()['totalbc'] ?? 0,
                'transactions' => array_slice($unconfirmedTxs->json()['txs'] ?? [], 0, 10),
                'mempool' => $stats->json()['n_tx'] ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Bitcoin data error: ' . $e->getMessage());
            return [
                'network' => 'Bitcoin',
                'error' => 'Failed to fetch Bitcoin data'
            ];
        }
    }

    /**
     * Get market data from CoinGecko
     */
    private function getMarketData()
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum',
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
                'include_24hr_vol' => 'true',
                'include_market_cap' => 'true'
            ]);

            $data = $response->json();

            return [
                'bitcoin' => [
                    'price' => $data['bitcoin']['usd'] ?? 0,
                    'change24h' => $data['bitcoin']['usd_24h_change'] ?? 0,
                    'volume24h' => $data['bitcoin']['usd_24h_vol'] ?? 0,
                    'marketCap' => $data['bitcoin']['usd_market_cap'] ?? 0
                ],
                'ethereum' => [
                    'price' => $data['ethereum']['usd'] ?? 0,
                    'change24h' => $data['ethereum']['usd_24h_change'] ?? 0,
                    'volume24h' => $data['ethereum']['usd_24h_vol'] ?? 0,
                    'marketCap' => $data['ethereum']['usd_market_cap'] ?? 0
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Market data error: ' . $e->getMessage());
            return [
                'bitcoin' => ['price' => 0, 'change24h' => 0],
                'ethereum' => ['price' => 0, 'change24h' => 0]
            ];
        }
    }

    /**
     * Get network statistics
     */
    private function getNetworkStats()
    {
        try {
            return [
                'totalNetworks' => 2,
                'activeConnections' => rand(1500, 2500),
                'avgBlockTime' => [
                    'bitcoin' => '10 min',
                    'ethereum' => '12 sec'
                ],
                'networkHealth' => 'excellent'
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch network stats'];
        }
    }

    /**
     * Search blockchain transactions
     */
    public function searchTransactions(Request $request)
    {
        $query = $request->input('query');
        $network = $request->input('network', 'ethereum');
        $limit = $request->input('limit', 20);

        try {
            if ($network === 'bitcoin') {
                return $this->searchBitcoinTransactions($query, $limit);
            } else {
                return $this->searchEthereumTransactions($query, $limit);
            }
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Search failed'
            ], 500);
        }
    }

    private function searchEthereumTransactions($query, $limit)
    {
        // If query looks like an address
        if (preg_match('/^0x[a-fA-F0-9]{40}$/', $query)) {
            $response = Http::get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'txlist',
                'address' => $query,
                'startblock' => 0,
                'endblock' => 99999999,
                'sort' => 'desc',
                'apikey' => $this->etherscanApiKey
            ]);

            return response()->json([
                'success' => true,
                'data' => array_slice($response->json()['result'] ?? [], 0, $limit),
                'type' => 'address_transactions'
            ]);
        }

        // If query looks like a transaction hash
        if (preg_match('/^0x[a-fA-F0-9]{64}$/', $query)) {
            $response = Http::get('https://api.etherscan.io/api', [
                'module' => 'proxy',
                'action' => 'eth_getTransactionByHash',
                'txhash' => $query,
                'apikey' => $this->etherscanApiKey
            ]);

            return response()->json([
                'success' => true,
                'data' => $response->json()['result'],
                'type' => 'transaction_details'
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid query format'
        ], 400);
    }

    private function searchBitcoinTransactions($query, $limit)
    {
        // If query looks like a Bitcoin address
        if (preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $query) || 
            preg_match('/^bc1[a-z0-9]{39,59}$/', $query)) {
            
            $response = Http::get("https://blockchain.info/rawaddr/{$query}?format=json&limit={$limit}");
            
            return response()->json([
                'success' => true,
                'data' => $response->json(),
                'type' => 'address_transactions'
            ]);
        }

        // If query looks like a transaction hash
        if (preg_match('/^[a-fA-F0-9]{64}$/', $query)) {
            $response = Http::get("https://blockchain.info/rawtx/{$query}?format=json");
            
            return response()->json([
                'success' => true,
                'data' => $response->json(),
                'type' => 'transaction_details'
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid Bitcoin query format'
        ], 400);
    }

    /**
     * Get price history for charts
     */
    public function getPriceHistory(Request $request)
    {
        $coin = $request->input('coin', 'bitcoin');
        $days = $request->input('days', 7);

        try {
            $response = Http::get("https://api.coingecko.com/api/v3/coins/{$coin}/market_chart", [
                'vs_currency' => 'usd',
                'days' => $days,
                'interval' => $days > 1 ? 'hourly' : 'minutely'
            ]);

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Price history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch price history'
            ], 500);
        }
    }
}