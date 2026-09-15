const Anthropic = require("@anthropic-ai/sdk");
const config = require("../config");

let client = null;

function getClient() {
  if (!config.anthropicApiKey) {
    throw new Error("MISSING_API_KEY");
  }
  if (!client) {
    client = new Anthropic({ apiKey: config.anthropicApiKey });
  }
  return client;
}

/**
 * Calls Claude with a single forced tool-use call and returns the tool's
 * parsed input. Throws if Claude does not call the expected tool.
 *
 * @param {object} opts
 * @param {string} opts.system - system prompt
 * @param {Array} opts.messages - Anthropic messages array (may include image content blocks)
 * @param {object} opts.tool - tool schema { name, description, input_schema }
 * @param {number} [opts.maxTokens]
 */
async function callWithForcedTool({ system, messages, tool, maxTokens = 4096 }) {
  const anthropic = getClient();

  const response = await anthropic.messages.create({
    model: config.anthropicModel,
    max_tokens: maxTokens,
    system,
    messages,
    tools: [tool],
    tool_choice: { type: "tool", name: tool.name },
  });

  const toolUseBlock = response.content.find(
    (block) => block.type === "tool_use" && block.name === tool.name
  );

  if (!toolUseBlock) {
    throw new Error("NO_TOOL_USE_RESPONSE");
  }

  return { input: toolUseBlock.input, usage: response.usage, stopReason: response.stop_reason };
}

module.exports = { callWithForcedTool };
