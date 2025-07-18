import './bootstrap';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

class BlockpeekApp {
    constructor() {
        this.priceChart = null;
        this.dashboardData = null;
        this.chatHistory = this.loadChatHistory();
        this.sessionId = this.generateSessionId();
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeChat();
        this.loadDashboardData();
        this.initializePriceChart();
        this.startRealTimeUpdates();
    }

    setupEventListeners() {
        // Chat functionality
        document.getElementById('send-message').addEventListener('click', () => this.sendMessage());
        document.getElementById('chat-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
        document.getElementById('clearChat').addEventListener('click', () => this.clearChat());

        // Search functionality
        document.getElementById('searchBtn').addEventListener('click', () => this.toggleSearchModal());
        document.getElementById('searchCancel').addEventListener('click', () => this.toggleSearchModal());
        document.getElementById('searchSubmit').addEventListener('click', () => this.performSearch());
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.performSearch();
        });

        // Chart period buttons
        document.querySelectorAll('.chart-period').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.chart-period').forEach(b => {
                    b.classList.remove('bg-blue-100', 'text-blue-700');
                    b.classList.add('hover:bg-gray-100');
                });
                e.target.classList.add('bg-blue-100', 'text-blue-700');
                e.target.classList.remove('hover:bg-gray-100');
                this.updatePriceChart(e.target.dataset.period);
            });
        });
    }

    initializeChat() {
        if (this.chatHistory.length === 0) {
            this.addChatMessage('ai', '👋 Hi! I\'m Blockpeek AI. Ask me anything about Bitcoin, Ethereum, or blockchain transactions. Try asking: "What\'s the latest Bitcoin price?" or "Show me recent Ethereum blocks"');
        } else {
            this.chatHistory.forEach(msg => this.addChatMessage(msg.role, msg.content, false));
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;

        // Add user message
        this.addChatMessage('user', message);
        input.value = '';

        // Add loading message
        const loadingMsg = this.addChatMessage('ai', 'Analyzing blockchain data...', false, true);

        try {
            // Process with enhanced blockchain context
            const response = await fetch('/api/chat/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    query: message,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Remove loading message
                loadingMsg.remove();
                
                // Create enhanced prompt with blockchain data
                const enhancedPrompt = data.enhanced_prompt;
                
                // Call Puter AI with enhanced prompt
                const aiResponse = await puter.ai.chat(enhancedPrompt, { 
                    model: "gpt-4.1-nano",
                    temperature: 0.7
                });
                
                const reply = aiResponse?.message?.content || aiResponse?.text || "I'm having trouble processing that request right now.";
                this.addChatMessage('ai', reply);
                
                // Show relevant blockchain data if available
                if (data.blockchain_data) {
                    this.updateDashboardWithChatData(data.blockchain_data);
                }
            } else {
                loadingMsg.remove();
                this.addChatMessage('ai', '⚠️ Sorry, I encountered an error processing your request. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            loadingMsg.remove();
            this.addChatMessage('ai', '⚠️ Sorry, I\'m having trouble connecting right now. Please try again.');
        }
    }

    addChatMessage(role, content, save = true, isLoading = false) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `max-w-xs lg:max-w-md px-4 py-2 rounded-2xl ${
            role === 'user' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-800'
        } ${isLoading ? 'animate-pulse' : ''}`;
        
        if (isLoading) {
            bubble.innerHTML = `
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600"></div>
                    <span>${content}</span>
                </div>
            `;
        } else {
            bubble.textContent = content;
        }
        
        messageDiv.appendChild(bubble);
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        if (save && !isLoading) {
            this.chatHistory.push({ role, content });
            this.saveChatHistory();
        }
        
        return messageDiv;
    }

    clearChat() {
        document.getElementById('chat-messages').innerHTML = '';
        this.chatHistory = [];
        this.saveChatHistory();
        this.initializeChat();
    }

    toggleSearchModal() {
        const modal = document.getElementById('searchModal');
        modal.classList.toggle('hidden');
        if (!modal.classList.contains('hidden')) {
            document.getElementById('searchInput').focus();
        }
    }

    async performSearch() {
        const query = document.getElementById('searchInput').value.trim();
        if (!query) return;

        this.toggleSearchModal();
        this.showLoading();

        try {
            const response = await fetch(`/api/search?query=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.hideLoading();
            
            if (data.success) {
                this.displaySearchResults(data.data, data.type);
            } else {
                this.addChatMessage('ai', `Search failed: ${data.error}`);
            }
        } catch (error) {
            this.hideLoading();
            this.addChatMessage('ai', 'Search failed. Please try again.');
        }
    }

    displaySearchResults(data, type) {
        let message = '';
        
        if (type === 'address_transactions') {
            message = `Found ${data.length || 0} transactions for the address. Here are the recent ones:`;
            if (data.length > 0) {
                data.slice(0, 5).forEach((tx, i) => {
                    message += `\n${i+1}. ${tx.hash?.substring(0, 20)}... - ${parseFloat(tx.value / 1e18).toFixed(4)} ETH`;
                });
            }
        } else if (type === 'transaction_details') {
            message = `Transaction Details:\nHash: ${data.hash}\nValue: ${parseFloat(data.value / 1e18).toFixed(4)} ETH\nFrom: ${data.from}\nTo: ${data.to}`;
        } else {
            message = `Found data: ${JSON.stringify(data).substring(0, 200)}...`;
        }
        
        this.addChatMessage('ai', message);
    }

    async loadDashboardData() {
        try {
            const response = await fetch('/api/dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.dashboardData = result.data;
                this.updateDashboard();
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    updateDashboard() {
        if (!this.dashboardData) return;

        const { market, bitcoin, ethereum } = this.dashboardData;
        
        // Update price cards
        if (market) {
            this.updatePriceCard('btc', market.bitcoin);
            this.updatePriceCard('eth', market.ethereum);
        }
        
        // Update block info
        if (bitcoin) {
            document.getElementById('btc-block').textContent = this.formatNumber(bitcoin.latestBlock);
        }
        
        if (ethereum) {
            document.getElementById('eth-block').textContent = this.formatNumber(ethereum.latestBlock);
        }
        
        // Update recent transactions
        this.updateRecentTransactions();
    }

    updatePriceCard(coin, data) {
        if (!data) return;
        
        const priceEl = document.getElementById(`${coin}-price`);
        const changeEl = document.getElementById(`${coin}-change`);
        
        if (priceEl) {
            priceEl.textContent = `$${this.formatNumber(data.price)}`;
        }
        
        if (changeEl) {
            const change = data.change24h || 0;
            changeEl.textContent = `${change >= 0 ? '+' : ''}${change.toFixed(2)}%`;
            changeEl.className = `text-sm ${change >= 0 ? 'text-green-600' : 'text-red-600'}`;
        }
    }

    updateRecentTransactions() {
        const container = document.getElementById('recent-transactions');
        container.innerHTML = '';
        
        if (this.dashboardData?.ethereum?.transactions) {
            this.dashboardData.ethereum.transactions.slice(0, 8).forEach(tx => {
                const txEl = document.createElement('div');
                txEl.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-xl';
                txEl.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium">${tx.hash?.substring(0, 20)}...</p>
                            <p class="text-xs text-gray-500">${this.formatTimeAgo(tx.timeStamp)}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium">${parseFloat(tx.value / 1e18).toFixed(4)} ETH</p>
                        <p class="text-xs text-gray-500">$${(parseFloat(tx.value / 1e18) * (this.dashboardData?.market?.ethereum?.price || 0)).toFixed(2)}</p>
                    </div>
                `;
                container.appendChild(txEl);
            });
        }
    }

    initializePriceChart() {
        const ctx = document.getElementById('priceChart').getContext('2d');
        this.priceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Bitcoin',
                    data: [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Ethereum',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        this.updatePriceChart(1);
    }

    async updatePriceChart(days = 1) {
        try {
            const [btcResponse, ethResponse] = await Promise.all([
                fetch(`/api/price-history?coin=bitcoin&days=${days}`),
                fetch(`/api/price-history?coin=ethereum&days=${days}`)
            ]);
            
            const btcData = await btcResponse.json();
            const ethData = await ethResponse.json();
            
            if (btcData.success && ethData.success) {
                const btcPrices = btcData.data.prices;
                const ethPrices = ethData.data.prices;
                
                const labels = btcPrices.map(point => new Date(point[0]));
                const btcValues = btcPrices.map(point => point[1]);
                const ethValues = ethPrices.map(point => point[1]);
                
                this.priceChart.data.labels = labels;
                this.priceChart.data.datasets[0].data = btcValues;
                this.priceChart.data.datasets[1].data = ethValues;
                this.priceChart.update();
            }
        } catch (error) {
            console.error('Failed to update price chart:', error);
        }
    }

    updateDashboardWithChatData(blockchainData) {
        if (blockchainData.market) {
            this.dashboardData = this.dashboardData || {};
            this.dashboardData.market = blockchainData.market;
            this.updateDashboard();
        }
    }

    startRealTimeUpdates() {
        // Update dashboard every 30 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, 30000);
        
        // Update price chart every 5 minutes
        setInterval(() => {
            if (this.priceChart) {
                this.updatePriceChart(1);
            }
        }, 300000);
    }

    showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    formatNumber(num) {
        if (num >= 1e6) {
            return (num / 1e6).toFixed(1) + 'M';
        } else if (num >= 1e3) {
            return (num / 1e3).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }

    formatTimeAgo(timestamp) {
        const now = Date.now() / 1000;
        const diff = now - timestamp;
        
        if (diff < 60) return 'just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return `${Math.floor(diff / 86400)}d ago`;
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    loadChatHistory() {
        try {
            return JSON.parse(localStorage.getItem('blockpeek_chat_history') || '[]');
        } catch {
            return [];
        }
    }

    saveChatHistory() {
        localStorage.setItem('blockpeek_chat_history', JSON.stringify(this.chatHistory));
    }
}

// Make BlockpeekApp available globally
window.BlockpeekApp = BlockpeekApp;