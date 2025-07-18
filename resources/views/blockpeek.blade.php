<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockpeek - Real-time Web3 Analytics</title>
    <meta name="description" content="Simple, beautiful blockchain analytics for everyone">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.puter.com/v2/"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.1); }
        .card-glow { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800">Blockpeek</span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></div>
                        <span class="text-sm text-gray-600">Live</span>
                    </div>
                    <button id="searchBtn" class="p-2 rounded-lg hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Real-time Web3 Analytics
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Track Bitcoin and Ethereum transactions in real-time. 
                    Ask questions in plain English and get instant insights.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 card-glow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Bitcoin Price</p>
                            <p id="btc-price" class="text-2xl font-bold text-gray-900">$--,---</p>
                            <p id="btc-change" class="text-sm text-green-600">+0.00%</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 card-glow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Ethereum Price</p>
                            <p id="eth-price" class="text-2xl font-bold text-gray-900">$--,---</p>
                            <p id="eth-change" class="text-sm text-green-600">+0.00%</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l8 12-8 4.5L4 14 12 2zm0 2.5L6.5 13.5 12 16l5.5-2.5L12 4.5z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 card-glow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">BTC Latest Block</p>
                            <p id="btc-block" class="text-2xl font-bold text-gray-900">---,---</p>
                            <p class="text-sm text-gray-500">~10 min ago</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 card-glow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">ETH Latest Block</p>
                            <p id="eth-block" class="text-2xl font-bold text-gray-900">---,---</p>
                            <p class="text-sm text-gray-500">~12 sec ago</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Charts Section -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Price Chart -->
                    <div class="bg-white rounded-2xl p-6 card-glow">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Price History</h3>
                            <div class="flex space-x-2">
                                <button class="chart-period px-3 py-1 text-sm rounded-lg bg-blue-100 text-blue-700" data-period="1">1D</button>
                                <button class="chart-period px-3 py-1 text-sm rounded-lg hover:bg-gray-100" data-period="7">7D</button>
                                <button class="chart-period px-3 py-1 text-sm rounded-lg hover:bg-gray-100" data-period="30">30D</button>
                            </div>
                        </div>
                        <div class="relative h-64">
                            <canvas id="priceChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white rounded-2xl p-6 card-glow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Transactions</h3>
                        <div class="space-y-3" id="recent-transactions">
                            <!-- Transactions will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Chat Section -->
                <div class="bg-white rounded-2xl p-6 card-glow">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Ask Blockpeek AI</h3>
                        <button id="clearChat" class="text-sm text-gray-500 hover:text-gray-700">Clear</button>
                    </div>
                    
                    <!-- Chat Messages -->
                    <div id="chat-messages" class="h-96 overflow-y-auto space-y-4 mb-4 p-4 bg-gray-50 rounded-xl">
                        <!-- Messages will be populated here -->
                    </div>
                    
                    <!-- Chat Input -->
                    <div class="flex space-x-2">
                        <input 
                            type="text" 
                            id="chat-input" 
                            placeholder="Ask about Bitcoin, Ethereum, or any blockchain..." 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button 
                            id="send-message" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Search Blockchain</h3>
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Address, transaction hash, or block number..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
            >
            <div class="flex space-x-3">
                <button id="searchCancel" class="flex-1 px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50">Cancel</button>
                <button id="searchSubmit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Search</button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading blockchain data...</span>
        </div>
    </div>

    <script>
        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            window.BlockpeekApp = new BlockpeekApp();
        });
    </script>
</body>
</html>