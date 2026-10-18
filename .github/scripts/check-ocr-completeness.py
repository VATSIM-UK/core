import json
import sys

path = sys.argv[1] if len(sys.argv) > 1 else "/tmp/ocr-result.json"

try:
    with open(path) as f:
        data = json.load(f)
except FileNotFoundError:
    print(f"::warning::OCR result not found at {path}; skipping completeness check")
    sys.exit(0)
except json.JSONDecodeError as e:
    print(f"::warning::Could not parse {path}: {e}; skipping completeness check")
    sys.exit(0)

status = data.get("status")
manifest = data.get("manifest") or {}
terminal = manifest.get("terminal_state")
warnings = data.get("warnings") or []
failed = (manifest.get("coverage") or {}).get("failed") or []

state = terminal if terminal is not None else status

if state == "skipped":
    print("OCR review skipped: no reviewable files")
    sys.exit(0)

if state not in ("success", "complete") or warnings or failed:
    print(
        "::error::OCR review incomplete: "
        f"status={status} terminal_state={terminal} "
        f"warnings={len(warnings)} failed_items={len(failed)}"
    )
    for item in failed:
        print(f"  - {item.get('path')}: {item.get('reason')}")
    sys.exit(1)

print("OCR review complete")
