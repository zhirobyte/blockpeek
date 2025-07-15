<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Blockpeek</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://js.puter.com/v2/"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @vite(['resources/css/app.css', 'resources/js/tracker.js'])
</head>

<body class="bg-white text-gray-850 font-sans">
  
  <h1 class="text-3xl font-bold text-blue-700 mb-2">🔍 Blockpeek</h1>
  <div class="min-h-screen flex flex-col lg:flex-row px-4 py-10 gap-6">
    
    
    <!-- 📊 Left Sidebar: Dashboard -->
<aside class="w-full lg:w-1/2 space-y-6">

      <div class="bg-white/80 border border-blue-100 shadow-lg rounded-2xl p-6 backdrop-blur-md">
        <h2 class="text-xl font-bold text-blue-600 mb-2">📈 Latest Block</h2>
        <p class="text-sm font-mono">Block: <span id="block-number" class="font-semibold">---</span></p>
        <p class="text-sm font-mono">Hash: <span id="block-hash">---</span></p>
        <p class="text-sm font-mono">Time: <span id="block-time">---</span></p>
      </div>

      <div class="bg-white/80 border border-blue-100 shadow-lg rounded-2xl p-6 backdrop-blur-md">
        <h2 class="text-xl font-bold text-blue-600 mb-2">🌐 Network</h2>
        <p class="text-sm">Ethereum <span class="text-green-500 ml-1">● Online</span></p>
      </div>

      <div class="bg-white/80 border border-blue-100 shadow-lg rounded-2xl p-6 backdrop-blur-md">
        <h2 class="text-xl font-bold text-blue-600 mb-2">📤 Transactions</h2>
       
            <!--- this is for dashboard eth -->
            <ul class="text-xs font-mono space-y-1 text-gray-700">
              @forelse ($txs as $tx)
                <li>
                  {{ \Illuminate\Support\Str::limit($tx['from'], 8) }}
                  →
                  {{ \Illuminate\Support\Str::limit($tx['to'], 8) }}
                  <span class="text-green-500">
                    {{ number_format(((float) $tx['value']) / 1e18, 4) }} ETH
                  </span>
                </li>
              @empty
                <li class="text-red-500">No transactions found or failed to load.</li>
              @endforelse
            </ul>

      </div>
    </aside>

    
<!-- 💬 Right Side: Chat UI -->
<main class="w-full lg:w-1/2 flex flex-col">
  <div class="bg-white/80 border border-blue-100 shadow-lg rounded-2xl p-6 backdrop-blur-md flex flex-col h-[85vh]">
    <h1 class="text-3xl font-bold text-blue-700 mb-2">Chat with me</h1>
    <p class="text-sm text-gray-500 mb-4">Ask anything about blockchain. Powered by AI.</p>

    <!-- 💬 Chat Box -->
    <div id="chat-box" class="flex-1 overflow-y-auto space-y-3 p-2 rounded-lg border border-blue-100 bg-gray-50">
      <!-- Messages will be inserted here -->
    </div>

    <!-- Input Field -->
    <textarea id="prompt" rows="3" placeholder="Ask about blockchain transactions..."
      class="mt-4 border border-blue-300 rounded-xl p-3 shadow focus:ring-2 focus:ring-blue-400 resize-none text-sm"></textarea>

    <!-- Ask Button -->
    <button onclick="askAI()"
      class="mt-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition">
      Ask AI
    </button>
  </div>
</main>


  

</body>
</html>
