const path = require("path");
require("dotenv").config({ path: path.resolve(__dirname, "../../.env") });

const axios = require("axios");

const apiKey = process.env.ETHERSCAN_API_KEY;
const address = "0xde0b295669a9fd93d5f28d9ec85e40f4cb697bae";

async function getLatestEthereumTx() {
  try {
    if (!apiKey) {
      console.error("Missing ETHERSCAN_API_KEY");
      console.log(JSON.stringify([])); // Still return empty
      return;
    }

    const res = await axios.get("https://api.etherscan.io/api", {
      params: {
        module: "account",
        action: "txlist",
        address,
        startblock: 0,
        endblock: 99999999,
        sort: "desc",
        apikey: apiKey,
      },
    });

    const latestTx = res.data?.result?.slice(0, 3) || [];
    console.log(JSON.stringify(latestTx));
  } catch (err) {
    console.error("Error fetching Ethereum transactions:", err.message);
    console.log(JSON.stringify([])); // Still return empty
  }
}

getLatestEthereumTx();
