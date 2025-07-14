
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blockpeek</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://js.puter.com/v2/"></script>
</head>
<body class="bg-white text-gray-800 font-sans">

  <div class="min-h-screen flex flex-col justify-center items-center px-4">
    <h1 class="text-3xl font-bold mb-4 text-blue-600">🔍 Blockpeek</h1>
    <p class="text-gray-600 mb-6 text-center">Ask blockchain anything. Powered by AI.</p>

    <div class="w-full max-w-xl">
      <textarea
        id="prompt"
        rows="4"
        placeholder="E.g. What's happening with BTC on-chain in Europe today?"
        class="w-full border border-blue-200 rounded-xl p-4 mb-4 shadow focus:outline-none focus:ring focus:ring-blue-300"
      ></textarea>

      <button
        onclick="askAI()"
        class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition"
      >
        Ask AI
      </button>

      <div id="response" class="mt-6 bg-gray-100 p-4 rounded-xl shadow text-sm hidden whitespace-pre-wrap"></div>
    </div>
  </div>

  <script>
    async function askAI() {
      const prompt = document.getElementById('prompt').value;
      const responseBox = document.getElementById('response');
      responseBox.classList.remove('hidden');
      responseBox.textContent = "Thinking... 🤔";

      try {
        const result = await puter.ai.chat(prompt, { model: "gpt-4.1-nano" });
        responseBox.textContent = result;
      } catch (err) {
        console.error(err);
        responseBox.textContent = "❌ Error getting AI response.";
      }
    }
  </script>

</body>
</html>
