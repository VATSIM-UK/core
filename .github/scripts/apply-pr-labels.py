import json
import os
import re
import subprocess
import sys

VALID_TYPES = {"feature", "fix", "chore", "docs", "refactor", "test"}
VALID_MISSING = {"tests", "copy", "docs", "migration"}

output_file = os.environ.get("OPENCODE_OUTPUT")
pr_number = os.environ.get("PR_NUMBER")

if not output_file or not pr_number:
    print("Missing required env vars: OPENCODE_OUTPUT, PR_NUMBER", file=sys.stderr)
    sys.exit(1)


def gh(args: str, stdin: str | None = None) -> str:
    return subprocess.run(
        f"gh {args}",
        shell=True,
        capture_output=True,
        text=True,
        input=stdin,
        env={**os.environ, "NO_COLOR": "1"},
        check=True,
    ).stdout.strip()


def extract_response(filepath: str) -> str:
    with open(filepath) as f:
        content = f.read().strip()

    # Try last line as JSON event from opencode stream
    try:
        event = json.loads(content.split("\n")[-1])
        if event.get("type") == "assistant" and isinstance(event.get("text"), str):
            return event["text"].strip()
    except (json.JSONDecodeError, IndexError):
        pass

    # Fallback: find JSON object with "type" and "missing" keys
    match = re.search(r'\{[^{}]*"type"\s*:\s*"[^"]+"[^{}]*"missing"\s*:[^{}]*\}', content)
    if match:
        return match.group(0).strip()

    return content


def validate(classification: dict) -> dict:
    t = classification.get("type", "")
    if t not in VALID_TYPES:
        raise ValueError(f'Invalid type: "{t}". Must be one of: {", ".join(sorted(VALID_TYPES))}')

    missing = classification.get("missing", [])
    if not isinstance(missing, list):
        raise ValueError("'missing' must be an array")

    for item in missing:
        if item not in VALID_MISSING:
            raise ValueError(f'Invalid missing item: "{item}". Must be one of: {", ".join(sorted(VALID_MISSING))}')

    return {
        "type": t,
        "missing": list(set(missing)),
        "reasoning": classification.get("reasoning"),
    }


try:
    raw = extract_response(output_file)
    print("Extracted:", raw[:200])

    cleaned = re.sub(r"```json\s*", "", raw)
    cleaned = re.sub(r"```\s*", "", cleaned).strip()

    classification = validate(json.loads(cleaned))
    print("Classification:", json.dumps(classification))

    desired = {f"type:{classification['type']}"}
    desired |= {f"needs-{item}" for item in classification["missing"]}
    print("Desired labels:", desired)

    current = set(gh(f"pr view {pr_number} --json labels --jq '.labels[].name'").split("\n"))
    current.discard("")
    print("Current labels:", current)

    managed_prefixes = ("type:", "needs-")

    for label in current:
        if label.startswith(managed_prefixes) and label not in desired:
            print(f"Removing: {label}")
            gh(f'pr edit {pr_number} --remove-label "{label}"')

    for label in desired:
        if label not in current:
            print(f"Adding: {label}")
            gh(f'pr edit {pr_number} --add-label "{label}"')

    print("Labels synced successfully")

except (ValueError, json.JSONDecodeError) as e:
    print(f"Classification failed: {e}", file=sys.stderr)
    sys.exit(1)
