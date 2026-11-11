import sys
from pathlib import Path

import pymupdf


def main():
    if len(sys.argv) not in (4, 5):
        print(
            "Usage: pdf_to_jpg.py <input.pdf> <output-directory> <filename-prefix> [dpi]",
            file=sys.stderr,
        )
        return 1

    input_path = Path(sys.argv[1])
    output_dir = Path(sys.argv[2])
    prefix = sys.argv[3]
    dpi = int(sys.argv[4]) if len(sys.argv) == 5 else 150
    created_files = []

    if dpi < 36 or dpi > 600:
        print("DPI must be between 36 and 600.", file=sys.stderr)
        return 1
    if not input_path.is_file():
        print("The uploaded PDF could not be found.", file=sys.stderr)
        return 1

    try:
        output_dir.mkdir(parents=True, exist_ok=True)
        with pymupdf.open(input_path) as document:
            if document.page_count == 0:
                print("The PDF contains no pages.", file=sys.stderr)
                return 1

            matrix = pymupdf.Matrix(dpi / 72, dpi / 72)
            for page_number, page in enumerate(document, start=1):
                pixmap = page.get_pixmap(
                    matrix=matrix,
                    colorspace=pymupdf.csRGB,
                    alpha=False,
                )
                output_path = output_dir / f"{prefix}_{page_number:03d}.jpg"
                pixmap.save(output_path, jpg_quality=85)
                created_files.append(output_path)

        print("\n".join(str(path) for path in created_files))
        return 0
    except Exception as exc:
        for path in created_files:
            path.unlink(missing_ok=True)
        print(f"PDF to JPG conversion failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
