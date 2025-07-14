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

      <!-- Input & Button -->
      <div class="flex gap-2">
        <textarea
          id="prompt"
          rows="2"
          placeholder="Ask anything about blockchain transactions..."
          class="flex-grow border border-blue-200 rounded-xl p-3 resize-none shadow focus:ring-2 focus:ring-blue-300"
        ></textarea>

        <button
          onclick="askAI()"
          class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl hover:bg-blue-700 transition shrink-0"
        >
          Ask
        </button>
      </div>
    </div>
  </div>

  <script>
    const chatBox = document.getElementById('chat-box');
    const promptInput = document.getElementById('prompt');

    // ✅ Load chat history OR show greeting
    window.onload = function () {
      const saved = JSON.parse(localStorage.getItem("blockpeek_chat") || "[]");

      if (saved.length === 0) {
        const welcome = "👋 Welcome to Blockpeek! Ask me anything about blockchain.";
        appendMessage("ai", welcome);
        saveToHistory("ai", welcome);
      } else {
        saved.forEach(m => appendMessage(m.role, m.text));
      }

      scrollToBottom();
    };

    // ✅ Handle Enter key for submitting
    promptInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        askAI();
      }
    });

    async function askAI() {
      const prompt = promptInput.value.trim();
      if (!prompt) return;

      // 🧹 /clear command
      if (prompt.toLowerCase() === "/clear") {
        chatBox.innerHTML = "";
        localStorage.removeItem("blockpeek_chat");
        promptInput.value = "";
        return;
      }

      appendMessage("user", prompt);
      saveToHistory("user", prompt);
      promptInput.value = "";
      scrollToBottom();

      // ⏳ Add spinner
      const thinkingId = appendMessage("ai", `
        <div class="flex items-center gap-2">
          <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="text-sm text-gray-600">Thinking...</span>
        </div>
      `, true); // mark temporary

      try {
        const res = await puter.ai.chat(prompt, { model: "gpt-4.1-nano" });

        replaceTempMessage(thinkingId, "ai", res);
        saveToHistory("ai", res);
        scrollToBottom();
      } catch (err) {
        console.error(err);
        replaceTempMessage(thinkingId, "ai", "⚠️ Failed to get a response.");
      }
    }

    // ✅ Append chat bubble
    function appendMessage(role, content, temporary = false) {
      const bubble = document.createElement("div");
      bubble.className = `mb-2 px-4 py-2 rounded-xl max-w-[80%] whitespace-pre-wrap ${
        role === "user"
          ? "bg-blue-600 text-white self-end ml-auto"
          : "bg-gray-200 text-gray-900 self-start"
      }`;
      bubble.innerHTML = content;
      if (temporary) bubble.dataset.temp = "true";
      chatBox.appendChild(bubble);
      return bubble;
    }

    // ✅ Replace last "thinking" with final answer
    function replaceTempMessage(tempEl, role, content) {
      if (tempEl && tempEl.dataset.temp === "true") {
        tempEl.innerHTML = content;
        tempEl.className = `mb-2 px-4 py-2 rounded-xl max-w-[80%] whitespace-pre-wrap ${
          role === "user"
            ? "bg-blue-600 text-white self-end ml-auto"
            : "bg-gray-200 text-gray-900 self-start"
        }`;
        delete tempEl.dataset.temp;
      }
    }

    // ✅ Save chat
    function saveToHistory(role, text) {
      const history = JSON.parse(localStorage.getItem("blockpeek_chat")) || [];
      history.push({ role, text });
      localStorage.setItem("blockpeek_chat", JSON.stringify(history));
    }

    // ✅ Auto-scroll to latest
    function scrollToBottom() {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  </script>

</body>
</html>
