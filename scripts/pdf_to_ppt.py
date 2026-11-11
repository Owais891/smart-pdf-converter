import sys
import unicodedata
from io import BytesIO

import pymupdf
from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.util import Pt


def safe_float(value, default=0.0):
    try:
        if value is None:
            return default
        result = float(value)
        return result if result == result else default
    except (TypeError, ValueError, OverflowError):
        return default


def reliable_text(text):
    if not text:
        return False
    cleaned = "".join(ch for ch in text if ch not in "\ufeff\uf0fe\ufffd\ud800")
    if not cleaned.strip():
        return False
    suspicious = 0
    useful = 0
    for ch in cleaned:
        category = unicodedata.category(ch)
        codepoint = ord(ch)
        if category in ("Cs", "Co") or 0xE000 <= codepoint <= 0xF8FF:
            suspicious += 1
        if category[0] in ("L", "N") or ch in ".,:;!?()/+-_–—":
            useful += 1
    if suspicious:
        return False
    return useful >= 1


def editable_lines(text_blocks, drawings):
    for block in text_blocks:
        for line in block.get("lines", []):
            spans = []
            previous_right = None
            unreadable_spans = []
            for span in line.get("spans", []):
                text = span.get("text", "")
                if not text or not reliable_text(text):
                    if text:
                        unreadable_spans.append(span)
                    continue

                rect = span.get("bbox")
                if spans and rect and previous_right is not None:
                    gap = rect[0] - previous_right
                    if gap > max(1.0, safe_float(span.get("size"), 12.0) * 0.12):
                        previous_text = spans[-1][1]
                        if not previous_text[-1:].isspace() and not text[:1].isspace():
                            spans.append((None, " "))

                spans.append((span, text))
                if rect:
                    previous_right = rect[2]

            rect = line.get("bbox")
            if (
                spans
                and rect
                and rect[2] > rect[0]
                and rect[3] > rect[1]
            ):
                first_span = next((span for span, _ in spans if span is not None), None)
                first_bbox = first_span.get("bbox") if first_span else None
                if not first_bbox:
                    continue

                font_size = safe_float(first_span.get("size"), 12.0)
                bullet_rect = None
                bullet_color = None
                for candidate in unreadable_spans:
                    candidate_bbox = candidate.get("bbox")
                    candidate_text = candidate.get("text", "")
                    is_bullet = any(
                        unicodedata.category(char) == "Co"
                        or char in "•▪▫■□●○◆◇‣⁃·"
                        for char in candidate_text
                    )
                    if (
                        is_bullet
                        and candidate_bbox
                        and candidate_bbox[2] - candidate_bbox[0] <= font_size * 0.8
                        and candidate_bbox[2] <= first_bbox[0]
                        and abs(
                            (candidate_bbox[1] + candidate_bbox[3]) / 2
                            - (rect[1] + rect[3]) / 2
                        ) <= font_size * 0.65
                    ):
                        bullet_rect = pymupdf.Rect(candidate_bbox)
                        color = candidate.get("color")
                        if isinstance(color, int):
                            bullet_color = (
                                ((color >> 16) & 255) / 255,
                                ((color >> 8) & 255) / 255,
                                (color & 255) / 255,
                            )
                        break

                if bullet_rect is None:
                    for drawing in drawings:
                        drawing_rect = drawing.get("rect")
                        fill = drawing.get("fill")
                        if not drawing_rect or not fill:
                            continue
                        candidate = pymupdf.Rect(drawing_rect)
                        if (
                            candidate.width < 2
                            or candidate.height < 2
                            or candidate.width > font_size * 0.8
                            or candidate.height > font_size * 0.8
                            or not 0.6 <= candidate.width / candidate.height <= 1.5
                            or candidate.x1 > first_bbox[0]
                            or first_bbox[0] - candidate.x1 > font_size * 1.5
                            or abs(candidate.y0 + candidate.y1 - rect[1] - rect[3])
                            > font_size * 1.2
                        ):
                            continue
                        bullet_rect = candidate
                        bullet_color = fill
                        break

                yield rect, spans, bullet_rect, bullet_color, first_bbox


def background_color(pixmap, rect):
    points = (
        (rect.x0 - 1, rect.y0 - 1),
        (rect.x1 + 1, rect.y0 - 1),
        (rect.x0 - 1, rect.y1 + 1),
        (rect.x1 + 1, rect.y1 + 1),
    )
    samples = []
    for x, y in points:
        x = max(0, min(int(x), pixmap.width - 1))
        y = max(0, min(int(y), pixmap.height - 1))
        samples.append(pixmap.pixel(x, y)[:3])

    return tuple(
        sorted(sample[channel] for sample in samples)[len(samples) // 2] / 255
        for channel in range(3)
    )


def main():
    if len(sys.argv) != 3:
        print("Usage: pdf_to_ppt.py <input.pdf> <output.pptx>", file=sys.stderr)
        return 1

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        source_pdf = pymupdf.open(input_path)
        if source_pdf.page_count == 0:
            print("The PDF contains no pages.", file=sys.stderr)
            return 1

        render_pdf = pymupdf.open(input_path)
        presentation = Presentation()
        first_page = source_pdf[0].rect
        presentation.slide_width = int(13.333333 * 914400)
        presentation.slide_height = int(
            presentation.slide_width * first_page.height / first_page.width
        )

        for page_index, source_page in enumerate(source_pdf):
            slide = presentation.slides.add_slide(presentation.slide_layouts[6])
            scale_x = presentation.slide_width / source_page.rect.width
            scale_y = presentation.slide_height / source_page.rect.height
            text_blocks = source_page.get_text("dict").get("blocks", [])

            # Redact extractable text from a separate copy; the raster keeps
            # non-text artwork and page backgrounds in their original positions.
            render_page = render_pdf[page_index]
            preview = render_page.get_pixmap(matrix=pymupdf.Matrix(1, 1), alpha=False)
            lines = list(editable_lines(text_blocks, source_page.get_drawings()))
            for _, line_spans, bullet_rect, _, _ in lines:
                if bullet_rect is not None:
                    redact_rect = pymupdf.Rect(
                        max(bullet_rect.x0 - 0.5, 0),
                        max(bullet_rect.y0 - 0.5, 0),
                        min(bullet_rect.x1 + 0.5, render_page.rect.width),
                        min(bullet_rect.y1 + 0.5, render_page.rect.height),
                    )
                    render_page.add_redact_annot(
                        redact_rect,
                        fill=background_color(preview, redact_rect),
                    )
                for span, _ in line_spans:
                    if span is None:
                        continue
                    span_bbox = span.get("bbox")
                    if not span_bbox:
                        continue
                    redact_rect = pymupdf.Rect(
                        max(span_bbox[0] - 0.5, 0),
                        max(span_bbox[1] - 0.5, 0),
                        min(span_bbox[2] + 0.5, render_page.rect.width),
                        min(span_bbox[3] + 0.5, render_page.rect.height),
                    )
                    render_page.add_redact_annot(
                        redact_rect,
                        fill=background_color(preview, redact_rect),
                    )
            render_page.apply_redactions()

            pixmap = render_page.get_pixmap(matrix=pymupdf.Matrix(2, 2), alpha=False)
            image_stream = BytesIO(pixmap.tobytes("png"))
            slide.shapes.add_picture(
                image_stream,
                0,
                0,
                presentation.slide_width,
                presentation.slide_height,
            )

            # Keep each visual line together so word spacing and reading order survive.
            page_width_inches = presentation.slide_width / 914400
            font_scale = page_width_inches / max(source_page.rect.width / 72, 1)
            for rect, line_spans, bullet_rect, bullet_color, first_bbox in lines:
                left_pt = bullet_rect.x0 if bullet_rect is not None else first_bbox[0]
                left = int(left_pt * scale_x)
                top = int(rect[1] * scale_y)
                width = max(int((rect[2] - left_pt) * scale_x), 91440)
                height = max(int((rect[3] - rect[1]) * scale_y), 182880)

                text_box = slide.shapes.add_textbox(left, top, width, height)
                text_frame = text_box.text_frame
                text_frame.clear()
                text_frame.margin_left = 0
                text_frame.margin_right = 0
                text_frame.margin_top = 0
                text_frame.margin_bottom = 0
                text_frame.word_wrap = False
                text_frame.auto_size = None

                paragraph = text_frame.paragraphs[0]
                if bullet_rect is not None:
                    bullet_run = paragraph.add_run()
                    bullet_run.text = "▪  "
                    bullet_run.font.name = "Arial"
                    first_span = next(span for span, _ in line_spans if span is not None)
                    bullet_run.font.size = Pt(
                        max(safe_float(first_span.get("size"), 12.0) * font_scale * 0.65, 6)
                    )
                    if bullet_color and len(bullet_color) >= 3:
                        bullet_run.font.color.rgb = RGBColor(
                            round(bullet_color[0] * 255),
                            round(bullet_color[1] * 255),
                            round(bullet_color[2] * 255),
                        )
                for span, span_text in line_spans:
                    run = paragraph.add_run()
                    run.text = span_text
                    if span is None:
                        continue

                    font = run.font
                    font_size = safe_float(span.get("size"), 12.0) * font_scale
                    font.size = Pt(max(font_size, 6))
                    font.name = "Arial"

                    font_name = str(span.get("font") or "")
                    if "Bold" in font_name or "bold" in font_name:
                        font.bold = True
                    if "Italic" in font_name or "Oblique" in font_name:
                        font.italic = True

                    color_value = span.get("color")
                    if isinstance(color_value, (int, float)):
                        color_integer = int(color_value)
                        red = (color_integer >> 16) & 255
                        green = (color_integer >> 8) & 255
                        blue = color_integer & 255
                        font.color.rgb = RGBColor(red, green, blue)

                    flags = span.get("flags", 0) or 0
                    if isinstance(flags, int) and flags & 16:
                        font.bold = True
                    if isinstance(flags, int) and flags & 2:
                        font.italic = True

        presentation.save(output_path)
        source_pdf.close()
        render_pdf.close()
        return 0
    except Exception as exc:
        print(str(exc), file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
