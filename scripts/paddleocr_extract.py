#!/usr/bin/env python3
import json
import os
import sys
import tempfile
from contextlib import redirect_stdout


def flatten_result(result):
    lines = []

    def visit(node):
        if isinstance(node, dict):
            for key in ("rec_texts", "text", "transcription"):
                value = node.get(key)
                if isinstance(value, str):
                    lines.append(value)
                elif isinstance(value, list):
                    lines.extend(str(item) for item in value if item)
            for value in node.values():
                visit(value)
            return

        if isinstance(node, (list, tuple)):
            if len(node) >= 2 and isinstance(node[1], (list, tuple)) and node[1]:
                text = node[1][0]
                if isinstance(text, str):
                    lines.append(text)
            for item in node:
                visit(item)

    visit(result)
    return "\n".join(dict.fromkeys(line.strip() for line in lines if line and line.strip()))


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "PDF path is required"}))
        return 2

    pdf_path = sys.argv[1]
    max_pages = int(sys.argv[2]) if len(sys.argv) > 2 else 3

    try:
        import fitz
        from paddleocr import PaddleOCR
    except Exception as exc:
        print(json.dumps({"error": f"dependency_missing: {exc}"}))
        return 3

    with redirect_stdout(sys.stderr):
        ocr = PaddleOCR(
            lang="en",
            text_detection_model_name="PP-OCRv5_mobile_det",
            text_recognition_model_name="en_PP-OCRv5_mobile_rec",
            use_doc_orientation_classify=False,
            use_doc_unwarping=False,
            use_textline_orientation=False,
        )

    doc = fitz.open(pdf_path)
    texts = []

    with tempfile.TemporaryDirectory() as temp_dir:
        for page_index in range(min(max_pages, len(doc))):
            image_path = os.path.join(temp_dir, f"page-{page_index + 1}.png")
            page = doc.load_page(page_index)
            pixmap = page.get_pixmap(matrix=fitz.Matrix(2, 2), alpha=False)
            pixmap.save(image_path)

            with redirect_stdout(sys.stderr):
                result = ocr.predict(image_path)

            texts.append(flatten_result(result))

    print(json.dumps({"text": "\n".join(text for text in texts if text).strip()}))
    return 0


if __name__ == "__main__":
    sys.exit(main())
