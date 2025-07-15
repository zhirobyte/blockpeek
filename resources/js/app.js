import './bootstrap';



    const chatBox = document.getElementById('chat-box');
    const promptInput = document.getElementById('prompt');
   
    window.onload = () => {
      const saved = JSON.parse(localStorage.getItem("blockpeek_chat") || "[]");
      if (saved.length === 0) {
        const greeting = "👋 Hi there! I'm Blockpeek AI. Ask me anything about blockchain transactions.";
        appendMessage("ai", greeting);
        saveToHistory("ai", greeting);
      } else {
        for (const m of saved) appendMessage(m.role, m.text);
      }
      scrollToBottom();
    };


    promptInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        askAI();
      }
    });

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

      const loading = appendMessage("ai", `
        <div class="flex items-center gap-2">
          <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="text-sm text-gray-600">Thinking...</span>
        </div>
      `, true);

      scrollToBottom();

      try {
        const res = await puter.ai.chat(prompt, { model: "gpt-4.1-nano" });
        loading.remove();
        const reply = res?.message?.content || res?.text || JSON.stringify(res);
        appendMessage("ai", reply);
        saveToHistory("ai", reply);
        scrollToBottom();
      } catch (err) {
        console.error(err);
        loading.remove();
        appendMessage("ai", "⚠️ Failed to get a response.");
      }
    }

    function appendMessage(role, text, returnNode = false) {
      const bubble = document.createElement("div");
      bubble.className = `mb-2 px-4 py-2 rounded-xl max-w-[80%] ${
        role === "user"
          ? "bg-blue-600 text-white self-end ml-auto"
          : "bg-gray-200 text-gray-900 self-start"
      }`;
      bubble.dataset.role = role;

      if (text.includes("<svg") || text.includes("</div>")) {
        bubble.innerHTML = text;
      } else {
        bubble.textContent = text;
      }

      chatBox.appendChild(bubble);
      return returnNode ? bubble : null;
    }

            function scrollToBottom() {
        const lastMessage = chatBox.lastElementChild;
        if (lastMessage) {
            lastMessage.scrollIntoView({ behavior: "smooth", block: "end" });
        }
        }


    function saveToHistory(role, text) {
      const history = JSON.parse(localStorage.getItem("blockpeek_chat") || "[]");
      history.push({ role, text });
      localStorage.setItem("blockpeek_chat", JSON.stringify(history));
    }
