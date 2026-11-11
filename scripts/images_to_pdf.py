import sys
from pathlib import Path

import pymupdf


def main():
    if len(sys.argv) < 3:
        print(
            "Usage: images_to_pdf.py <output.pdf> <image-1> [image-2 ...]",
            file=sys.stderr,
        )
        return 1

    output_path = Path(sys.argv[1])
    image_paths = [Path(path) for path in sys.argv[2:]]
    temporary_path = output_path.with_name(output_path.stem + ".tmp.pdf")
    document = pymupdf.open()

    try:
        if not image_paths:
            raise ValueError("No images were provided.")

        for image_path in image_paths:
            if not image_path.is_file():
                raise FileNotFoundError(f"Image file was not found: {image_path.name}")

            try:
                pixmap = pymupdf.Pixmap(str(image_path))
            except Exception as exc:
                raise ValueError(f"Could not read image {image_path.name}: {exc}") from exc

            width = pixmap.width * 72 / 150
            height = pixmap.height * 72 / 150
            page = document.new_page(width=width, height=height)
            page.insert_image(page.rect, filename=str(image_path), keep_proportion=False)

        output_path.parent.mkdir(parents=True, exist_ok=True)
        document.save(temporary_path, garbage=4, deflate=True)
        document.close()
        temporary_path.replace(output_path)
        return 0
    except Exception as exc:
        document.close()
        temporary_path.unlink(missing_ok=True)
        print(f"JPG to PDF conversion failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
