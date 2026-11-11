import re
import sys

import pymupdf
from docx import Document
from docx.enum.section import WD_SECTION
from docx.shared import Pt, RGBColor


FONT_ALIASES = {
    "arial": "Arial",
    "calibri": "Calibri",
    "cambria": "Cambria",
    "courier": "Courier New",
    "times": "Times New Roman",
    "timesnewroman": "Times New Roman",
    "symbol": "Symbol",
}
FONT_FLAGS_BOLD = 16
FONT_FLAGS_ITALIC = 2
FONT_FLAGS_UNDERLINE = 1


def word_font_name(pdf_font):
    name = pdf_font.rsplit("+", 1)[-1]
    normalized = re.sub(r"[^a-z]", "", name.lower())
    for prefix, family in FONT_ALIASES.items():
        if normalized.startswith(prefix):
            return family
    return name or "Arial"


def configure_page(section, page):
    section.page_width = Pt(page.rect.width)
    section.page_height = Pt(page.rect.height)
    section.top_margin = Pt(0)
    section.bottom_margin = Pt(0)
    section.left_margin = Pt(0)
    section.right_margin = Pt(0)
    section.header_distance = Pt(0)
    section.footer_distance = Pt(0)


def add_pdf_line(document, line, previous_bottom):
    spans = [span for span in line["spans"] if span.get("text")]
    if not spans:
        return previous_bottom

    x0, y0, _, y1 = line["bbox"]
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.left_indent = Pt(max(0, x0))
    paragraph.paragraph_format.first_line_indent = Pt(0)
    paragraph.paragraph_format.space_before = Pt(max(0, y0 - previous_bottom))
    paragraph.paragraph_format.space_after = Pt(0)
    paragraph.paragraph_format.line_spacing = Pt(max(1, y1 - y0))
    paragraph.paragraph_format.keep_together = True

    for span in spans:
        run = paragraph.add_run(span["text"])
        font = run.font
        font.name = word_font_name(span.get("font", ""))
        font.size = Pt(max(1, span.get("size", 11)))
        flags = span.get("flags", 0)
        font.bold = bool(flags & FONT_FLAGS_BOLD)
        font.italic = bool(flags & FONT_FLAGS_ITALIC)
        font.underline = bool(span.get("char_flags", 0) & FONT_FLAGS_UNDERLINE)
        run._element.get_or_add_rPr().rFonts.set(
            "{http://schemas.openxmlformats.org/wordprocessingml/2006/main}eastAsia",
            font.name,
        )
        color = span.get("color")
        if isinstance(color, int):
            font.color.rgb = RGBColor((color >> 16) & 255, (color >> 8) & 255, color & 255)

    return y1


if len(sys.argv) != 3:
    print("Usage: pdf_to_docx.py <input.pdf> <output.docx>")
    raise SystemExit(2)

input_path, output_path = sys.argv[1], sys.argv[2]
document = Document()
extracted_any_text = False

try:
    pdf = pymupdf.open(input_path)
except Exception as error:
    print("Could not read the PDF: {}".format(error))
    raise SystemExit(3)

try:
    for page_number, page in enumerate(pdf):
        if page_number:
            section = document.add_section(WD_SECTION.NEW_PAGE)
            configure_page(section, page)
        else:
            configure_page(document.sections[0], page)

        page_data = page.get_text("dict", sort=True)
        lines = []
        for block in page_data["blocks"]:
            if block.get("type") == 0:
                lines.extend(block.get("lines", []))

        lines.sort(key=lambda line: (line["bbox"][1], line["bbox"][0]))
        previous_bottom = 0
        for line in lines:
            if any(span.get("text", "").strip() for span in line["spans"]):
                extracted_any_text = True
            previous_bottom = add_pdf_line(document, line, previous_bottom)
finally:
    pdf.close()

if not extracted_any_text:
    print("No selectable text was found. Run OCR on scanned PDFs before converting.")
    raise SystemExit(4)

document.save(output_path)
