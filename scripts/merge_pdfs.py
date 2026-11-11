import sys
from pathlib import Path

import pymupdf


def main():
    if len(sys.argv) < 3:
        print("Usage: merge_pdfs.py <output.pdf> <input-1.pdf> [input-2.pdf ...]", file=sys.stderr)
        return 1

    output_path = Path(sys.argv[1])
    input_paths = [Path(path) for path in sys.argv[2:]]
    temporary_path = output_path.with_name(output_path.stem + ".tmp.pdf")
    merged = pymupdf.open()

    try:
        for input_path in input_paths:
            if not input_path.is_file():
                raise FileNotFoundError(f"PDF file was not found: {input_path.name}")

            with pymupdf.open(input_path) as source:
                if source.is_encrypted and not source.authenticate(""):
                    raise ValueError(f"PDF is password-protected: {input_path.name}")
                if source.page_count == 0:
                    raise ValueError(f"PDF contains no pages: {input_path.name}")
                merged.insert_pdf(
                    source,
                    links=True,
                    annots=True,
                    widgets=True,
                    join_duplicates=True,
                )

        if merged.page_count == 0:
            raise ValueError("No pages were found in the selected PDFs.")

        output_path.parent.mkdir(parents=True, exist_ok=True)
        merged.save(temporary_path, garbage=4, deflate=True)
        merged.close()
        temporary_path.replace(output_path)
        return 0
    except Exception as exc:
        merged.close()
        temporary_path.unlink(missing_ok=True)
        print(f"PDF merge failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
