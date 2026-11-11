import sys
from io import BytesIO

import pymupdf
from docx import Document
from docx.enum.section import WD_SECTION
from docx.shared import Pt


def configure_page(section, page):
    section.page_width = Pt(page.rect.width)
    section.page_height = Pt(page.rect.height)
    section.top_margin = Pt(0)
    section.bottom_margin = Pt(0)
    section.left_margin = Pt(0)
    section.right_margin = Pt(0)
    section.header_distance = Pt(0)
    section.footer_distance = Pt(0)


def add_pdf_page(document, page):
    pixmap = page.get_pixmap(matrix=pymupdf.Matrix(2, 2), alpha=False)
    image = BytesIO(pixmap.tobytes("png"))

    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(0)
    paragraph.paragraph_format.space_after = Pt(0)
    paragraph.paragraph_format.line_spacing = 1
    paragraph.paragraph_format.keep_together = True
    paragraph.add_run().add_picture(
        image,
        width=Pt(page.rect.width),
        height=Pt(page.rect.height),
    )


if len(sys.argv) != 3:
    print("Usage: pdf_to_docx.py <input.pdf> <output.docx>")
    raise SystemExit(2)

input_path, output_path = sys.argv[1], sys.argv[2]
try:
    pdf = pymupdf.open(input_path)
except Exception as error:
    print("Could not read the PDF: {}".format(error))
    raise SystemExit(3)

if pdf.needs_pass and not pdf.authenticate(""):
    pdf.close()
    print("This PDF is password-protected. Use the Unlock PDF tool with its password before converting.")
    raise SystemExit(5)

if pdf.page_count == 0:
    pdf.close()
    print("The PDF has no pages to convert.")
    raise SystemExit(6)

document = Document()
try:
    for page_number, page in enumerate(pdf):
        section = (
            document.sections[0]
            if page_number == 0
            else document.add_section(WD_SECTION.NEW_PAGE)
        )
        configure_page(section, page)
        add_pdf_page(document, page)
except Exception as error:
    print("Could not render PDF page {}: {}".format(page_number + 1, error))
    raise SystemExit(7)
finally:
    pdf.close()

document.save(output_path)
