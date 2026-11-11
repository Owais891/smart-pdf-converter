import sys
from pathlib import Path

import pymupdf


def save_document(document, path):
    document.save(path, garbage=4, deflate=True)


def main():
    if len(sys.argv) != 7:
        print(
            "Usage: split_pdf.py <input.pdf> <output-dir> <prefix> <mode> <from-page> <to-page>",
            file=sys.stderr,
        )
        return 1

    input_path = Path(sys.argv[1])
    output_dir = Path(sys.argv[2])
    prefix = sys.argv[3]
    mode = sys.argv[4]
    from_page = int(sys.argv[5])
    to_page = int(sys.argv[6])
    generated = []

    if mode not in ("each", "range"):
        print("Invalid split mode.", file=sys.stderr)
        return 1
    if from_page < 1 or to_page < 0:
        print("Invalid page range.", file=sys.stderr)
        return 1
    if not input_path.is_file():
        print("The uploaded PDF could not be found.", file=sys.stderr)
        return 1

    try:
        output_dir.mkdir(parents=True, exist_ok=True)
        with pymupdf.open(input_path) as source:
            if source.is_encrypted and not source.authenticate(""):
                raise ValueError("The PDF is password-protected.")
            if to_page == 0:
                to_page = source.page_count
            if to_page < from_page:
                raise ValueError("Invalid page range.")
            if from_page > source.page_count:
                raise ValueError(f"The PDF has only {source.page_count} page(s).")
            if to_page > source.page_count:
                raise ValueError(f"The PDF has only {source.page_count} page(s).")

            if mode == "range":
                output_path = output_dir / f"{prefix}_pages_{from_page}-{to_page}.pdf"
                part = pymupdf.open()
                try:
                    part.insert_pdf(
                        source,
                        from_page=from_page - 1,
                        to_page=to_page - 1,
                        links=True,
                        annots=True,
                        widgets=True,
                        join_duplicates=True,
                    )
                    save_document(part, output_path)
                finally:
                    part.close()
                generated.append(output_path)
            else:
                for page_number in range(from_page, to_page + 1):
                    output_path = output_dir / f"{prefix}_page_{page_number:03d}.pdf"
                    part = pymupdf.open()
                    try:
                        part.insert_pdf(
                            source,
                            from_page=page_number - 1,
                            to_page=page_number - 1,
                            links=True,
                            annots=True,
                            widgets=True,
                            join_duplicates=True,
                        )
                        save_document(part, output_path)
                    finally:
                        part.close()
                    generated.append(output_path)

        print("\n".join(str(path) for path in generated))
        return 0
    except Exception as exc:
        for path in generated:
            path.unlink(missing_ok=True)
        print(f"PDF split failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
