#!/usr/bin/env python3
import json
import sys


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "PDF path is required"}))
        return 2

    pdf_path = sys.argv[1]
    max_pages = int(sys.argv[2]) if len(sys.argv) > 2 else 2

    try:
        import fitz
    except Exception as exc:
        print(json.dumps({"error": f"dependency_missing: {exc}"}))
        return 3

    try:
        doc = fitz.open(pdf_path)
        texts = []

        for page_index in range(min(max_pages, len(doc))):
            text = doc.load_page(page_index).get_text("text")
            if text:
                texts.append(text)

        print(json.dumps({"text": "\n".join(texts).strip()}))
        return 0
    except Exception as exc:
        print(json.dumps({"error": str(exc)}))
        return 4


if __name__ == "__main__":
    sys.exit(main())
