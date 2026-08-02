import json
import os
import re
import subprocess
import sys

VALID_TYPES = {"feature", "fix", "chore", "docs", "refactor", "test"}
VALID_AREAS = {"membership", "admin", "training", "auth", "site", "atc", "roster", "api", "integrations", "infrastructure"}
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


def extract_json(text: str) -> str:
    text = re.sub(r"```(?:json)?\s*", "", text)
    text = re.sub(r"```\s*", "", text).strip()

    start = text.find("{")
    if start == -1:
        return text

    depth = 0
    for i, ch in enumerate(text[start:], start):
        if ch == "{":
            depth += 1
        elif ch == "}":
            depth -= 1
            if depth == 0:
                return text[start : i + 1]

    return text


def validate(classification: dict) -> dict:
    t = classification.get("type", "")
    if t not in VALID_TYPES:
        raise ValueError(f'Invalid type: "{t}". Must be one of: {", ".join(sorted(VALID_TYPES))}')

    areas = classification.get("area", [])
    if not isinstance(areas, list):
        raise ValueError("'area' must be an array")

    for item in areas:
        if item not in VALID_AREAS:
            raise ValueError(f'Invalid area: "{item}". Must be one of: {", ".join(sorted(VALID_AREAS))}')

    missing = classification.get("missing", [])
    if not isinstance(missing, list):
        raise ValueError("'missing' must be an array")

    for item in missing:
        if item not in VALID_MISSING:
            raise ValueError(f'Invalid missing item: "{item}". Must be one of: {", ".join(sorted(VALID_MISSING))}')

    return {
        "type": t,
        "area": list(set(areas)),
        "missing": list(set(missing)),
        "reasoning": classification.get("reasoning"),
    }


try:
    with open(output_file) as f:
        raw = f.read().strip()

    json_str = extract_json(raw)
    print("Extracted:", json_str[:200])

    classification = validate(json.loads(json_str))
    print("Classification:", json.dumps(classification))

    desired = {f"type:{classification['type']}"}
    desired |= {f"area:{item}" for item in classification["area"]}
    desired |= {f"needs-{item}" for item in classification["missing"]}
    print("Desired labels:", desired)

    current = set(gh(f"pr view {pr_number} --json labels --jq '.labels[].name'").split("\n"))
    current.discard("")
    print("Current labels:", current)

    managed_prefixes = ("type:", "area:", "needs-")

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
