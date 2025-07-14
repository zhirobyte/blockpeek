<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Blockpeek</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://js.puter.com/v2/"></script>
</head>
<body class="bg-white text-gray-800 font-sans">

  <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10">
    <div class="w-full max-w-2xl">
      <h1 class="text-3xl font-bold text-blue-600 mb-2">🔍 Blockpeek</h1>
      <p class="text-gray-500 mb-6">Ask about the blockchain. Powered by AI.</p>

      <!-- Chat Box -->
      <div id="chat-box" class="bg-gray-50 border border-blue-100 rounded-xl p-4 h-[400px] overflow-y-auto shadow mb-4 space-y-4 text-sm">
        <!-- Messages will be appended here -->
      </div>

      <!-- Input Area -->
      <textarea
        id="prompt"
        rows="3"
        placeholder="Ask anything about blockchain transactions..."
        class="w-full border border-blue-200 rounded-xl p-4 resize-none shadow focus:ring-2 focus:ring-blue-300 mb-2"
      ></textarea>

      <button
        onclick="askAI()"
        class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition"
      >
        Ask AI
      </button>
    </div>
  </div>

  <script>
    const chatBox = document.getElementById('chat-box');
    const promptInput = document.getElementById('prompt');

    // Load chat history from localStorage
    window.onload = function () {
      const savedHistory = JSON.parse(localStorage.getItem('blockpeek_chat')) || [];
      savedHistory.forEach(msg => appendMessage(msg.role, msg.text));
      chatBox.scrollTop = chatBox.scrollHeight;
    };

    // Press Enter = Submit (Shift+Enter for newline)
    promptInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askAI();
      }
    });

    async function askAI() {
      const prompt = promptInput.value.trim();
      if (!prompt) return;

      appendMessage("user", prompt);
      saveToHistory("user", prompt);
      promptInput.value = "";
      chatBox.scrollTop = chatBox.scrollHeight;

      appendMessage("ai", "Thinking... 🤖");

      try {
        const res = await puter.ai.chat(prompt, { model: "gpt-4.1-nano" });

        // Remove "Thinking..." message
        removeLastMessage("ai");
        appendMessage("ai", res);
        saveToHistory("ai", res);
        chatBox.scrollTop = chatBox.scrollHeight;

      } catch (err) {
        console.error(err);
        removeLastMessage("ai");
        appendMessage("ai", "❌ Failed to get AI response.");
      }
    }

    function appendMessage(role, text) {
      const msg = document.createElement("div");
      msg.className = role === "user"
        ? "text-right text-blue-600"
        : "text-left text-gray-800";
      msg.innerHTML = `<div class="inline-block bg-${role === 'user' ? 'blue' : 'gray'}-100 p-3 rounded-xl shadow">${text}</div>`;
      chatBox.appendChild(msg);
    }

    function removeLastMessage(role) {
      const messages = chatBox.querySelectorAll("div");
      for (let i = messages.length - 1; i >= 0; i--) {
        if (messages[i].className.includes(role === "user" ? "text-right" : "text-left")) {
          messages[i].remove();
          break;
        }
      }
    }

    function saveToHistory(role, text) {
      const history = JSON.parse(localStorage.getItem('blockpeek_chat')) || [];
      history.push({ role, text });
      localStorage.setItem('blockpeek_chat', JSON.stringify(history));
    }
  </script>

</body>
</html>
