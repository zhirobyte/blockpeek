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

    // ✅ Load chat history OR show greeting
    window.onload = function () {
      const savedHistory = JSON.parse(localStorage.getItem('blockpeek_chat')) || [];

      if (savedHistory.length === 0) {
        const greeting = "👋 Hi there! I'm Blockpeek AI. Ask me anything about blockchain transactions.";
        appendMessage("ai", greeting);
        saveToHistory("ai", greeting);
      } else {
        savedHistory.forEach(msg => appendMessage(msg.role, msg.text));
      }

      scrollToBottom();
    };

    // ✅ Handle Enter key
    promptInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askAI();
      }
    });

    // ✅ Main Chat Logic
          async function askAI() {
        const prompt = promptInput.value.trim();
        if (!prompt) return;

        if (prompt.toLowerCase() === "/clear") {
          chatBox.innerHTML = "";
          localStorage.removeItem("blockpeek_chat");
          promptInput.value = "";
          return;
        }

        appendMessage("user", prompt);
        saveToHistory("user", prompt);
        promptInput.value = "";

        appendMessage("ai", `
          <div class="flex items-center gap-2">
            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-gray-600">Thinking...</span>
          </div>
        `);

        scrollToBottom();

        try {
          const res = await puter.ai.chat(prompt, { model: "gpt-4.1-nano" });

          removeLastMessage("ai");

          const reply = res?.message?.content || res?.text || JSON.stringify(res);
          appendMessage("ai", reply);
          saveToHistory("ai", reply);

          scrollToBottom();
        } catch (err) {
          console.error(err);
          removeLastMessage("ai");
          appendMessage("ai", "⚠️ Failed to get a response.");
        }
      }


    // ✅ Append message to chat box
    function appendMessage(sender, text) {
  const bubble = document.createElement("div");
  bubble.className = `mb-2 px-4 py-2 rounded-xl max-w-[80%] ${
    sender === "user"
      ? "bg-blue-600 text-white self-end ml-auto"
      : "bg-gray-200 text-gray-900 self-start"
  }`;
  bubble.dataset.role = sender;

  // ✅ If it looks like HTML (like SVG), render it as HTML
  if (text.includes("<svg") || text.includes("</div>")) {
    bubble.innerHTML = text;
  } else {
    bubble.textContent = text;
  }

  chatBox.appendChild(bubble);
}


    // ✅ Remove spinner
    function removeLastMessage(role) {
      const messages = Array.from(chatBox.children).reverse();
      for (const msg of messages) {
        if (msg.dataset.role === role) {
          msg.remove();
          break;
        }
      }
    }

    // ✅ Save to local storage
    function saveToHistory(role, text) {
      const history = JSON.parse(localStorage.getItem('blockpeek_chat')) || [];
      history.push({ role, text });
      localStorage.setItem('blockpeek_chat', JSON.stringify(history));
    }

    function scrollToBottom() {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  </script>

</body>
</html>
