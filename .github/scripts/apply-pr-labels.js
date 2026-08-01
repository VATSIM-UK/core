const fs = require("node:fs");
const path = require("node:path");
const { execSync } = require("node:child_process");

const VALID_TYPES = ["feature", "fix", "chore", "docs", "refactor", "test"];
const VALID_MISSING = ["tests", "copy", "docs", "migration"];

const outputFile = process.env.OPENCODE_OUTPUT;
const prNumber = process.env.PR_NUMBER;

if (!outputFile || !prNumber) {
  console.error("Missing required env vars: OPENCODE_OUTPUT, PR_NUMBER");
  process.exit(1);
}

function extractModelResponse(filePath) {
  const content = fs.readFileSync(filePath, "utf-8");
  const lines = content.trim().split("\n");

  const lastLine = lines[lines.length - 1];
  try {
    const event = JSON.parse(lastLine);
    if (event.type === "assistant" && typeof event.text === "string") {
      return event.text.trim();
    }
  } catch {
    // not JSON, treat whole content as text
  }

  // Fallback: try to find JSON in the raw output
  const match = content.match(/\{[\s\S]*"type"[\s\S]*"missing"[\s\S]*\}/);
  if (match) return match[0].trim();

  return content.trim();
}

function validateClassification(obj) {
  if (!obj || typeof obj !== "object") {
    throw new Error("Classification is not an object");
  }

  if (!VALID_TYPES.includes(obj.type)) {
    throw new Error(
      `Invalid type: "${obj.type}". Must be one of: ${VALID_TYPES.join(", ")}`
    );
  }

  if (!Array.isArray(obj.missing)) {
    throw new Error("'missing' must be an array");
  }

  for (const item of obj.missing) {
    if (!VALID_MISSING.includes(item)) {
      throw new Error(
        `Invalid missing item: "${item}". Must be one of: ${VALID_MISSING.join(", ")}`
      );
    }
  }

  obj.missing = [...new Set(obj.missing)];

  return {
    type: obj.type,
    missing: obj.missing,
    reasoning: typeof obj.reasoning === "string" ? obj.reasoning : null,
  };
}

function gh(args, stdin) {
  return execSync(`gh ${args}`, {
    encoding: "utf-8",
    input: stdin || undefined,
    env: { ...process.env, NO_COLOR: "1" },
  }).trim();
}

try {
  const rawText = extractModelResponse(outputFile);
  console.log("Extracted response:", rawText.slice(0, 200));

  const cleaned = rawText
    .replace(/```json\s*/g, "")
    .replace(/```\s*/g, "")
    .trim();

  const parsed = JSON.parse(cleaned);
  const classification = validateClassification(parsed);
  console.log("Classification:", JSON.stringify(classification));

  const desiredLabels = new Set([`type:${classification.type}`]);
  for (const item of classification.missing) {
    desiredLabels.add(`needs-${item}`);
  }
  console.log("Desired labels:", [...desiredLabels]);

  const currentLabelsJson = gh(
    `pr view ${prNumber} --json labels --jq '.labels[].name'`
  );
  const currentLabels = new Set(
    currentLabelsJson.split("\n").filter(Boolean)
  );
  console.log("Current labels:", [...currentLabels]);

  const managedPrefixes = ["type:", "needs-"];

  for (const label of currentLabels) {
    if (
      managedPrefixes.some((p) => label.startsWith(p)) &&
      !desiredLabels.has(label)
    ) {
      console.log(`Removing label: ${label}`);
      gh(`pr edit ${prNumber} --remove-label "${label}"`);
    }
  }

  for (const label of desiredLabels) {
    if (!currentLabels.has(label)) {
      console.log(`Adding label: ${label}`);
      gh(`pr edit ${prNumber} --add-label "${label}"`);
    }
  }

  console.log("Labels synced successfully");
} catch (err) {
  console.error("Classification failed:", err.message);
  if (err.message.includes("Invalid ") || err instanceof SyntaxError) {
    console.error(
      "Model returned unparseable or invalid output. No labels applied."
    );
  }
  process.exit(1);
}
