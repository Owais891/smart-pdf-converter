import sys
from pathlib import Path

import pymupdf


def main():
    if len(sys.argv) != 4:
        print("Usage: rotate_pdf.py <input.pdf> <output.pdf> <degrees>", file=sys.stderr)
        return 1

    input_path = Path(sys.argv[1])
    output_path = Path(sys.argv[2])
    temporary_path = output_path.with_name(output_path.stem + ".tmp.pdf")

    try:
        degrees = int(sys.argv[3])
        if degrees not in (90, 180, 270):
            raise ValueError("Rotation must be 90, 180, or 270 degrees.")
        if not input_path.is_file():
            raise FileNotFoundError("The uploaded PDF could not be found.")

        with pymupdf.open(input_path) as document:
            if document.is_encrypted and not document.authenticate(""):
                raise ValueError("The PDF is password-protected.")
            if document.page_count == 0:
                raise ValueError("The PDF contains no pages.")

            for page in document:
                page.set_rotation((page.rotation + degrees) % 360)

            output_path.parent.mkdir(parents=True, exist_ok=True)
            document.save(temporary_path, garbage=4, deflate=True)

        temporary_path.replace(output_path)
        return 0
    except Exception as exc:
        temporary_path.unlink(missing_ok=True)
        print(f"PDF rotation failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
