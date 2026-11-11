import sys
from pathlib import Path

import pdfplumber
from openpyxl import Workbook
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter


THIN_GRAY = Side(style="thin", color="FF9A9A9A")
TABLE_BORDER = Border(
    left=THIN_GRAY,
    right=THIN_GRAY,
    top=THIN_GRAY,
    bottom=THIN_GRAY,
)


def excel_value(value):
    if value is None:
        return None
    text = str(value).strip()
    if not text:
        return None
    if text.isdigit() and (text == "0" or not text.startswith("0")):
        return int(text)
    try:
        if text.count(".") == 1 and text.replace(".", "", 1).isdigit():
            return float(text)
    except ValueError:
        pass
    return text


def color_hex(color):
    if isinstance(color, (tuple, list)) and len(color) >= 3:
        channels = [max(0, min(255, round(float(value) * 255))) for value in color[:3]]
        return "FF{:02X}{:02X}{:02X}".format(*channels)
    return None


def cell_font(page, bbox):
    x0, top, x1, bottom = bbox
    chars = [
        char for char in page.chars
        if x0 <= (char["x0"] + char["x1"]) / 2 <= x1
        and top <= (char["top"] + char["bottom"]) / 2 <= bottom
        and char.get("text", "").strip()
    ]
    if not chars:
        return Font(name="Calibri", size=11)

    styles = {}
    for char in chars:
        key = (
            char.get("fontname", "Calibri"),
            round(float(char.get("size", 11)), 1),
            color_hex(char.get("non_stroking_color")),
        )
        styles[key] = styles.get(key, 0) + 1
    font_name, font_size, font_color = max(styles, key=styles.get)
    font_name = font_name.rsplit("+", 1)[-1]
    normalized_name = font_name.lower()
    return Font(
        name=font_name,
        size=font_size,
        bold="bold" in normalized_name,
        italic="italic" in normalized_name or "oblique" in normalized_name,
        color=font_color or "FF000000",
    )


def cell_fill(page, bbox):
    x0, top, x1, bottom = bbox
    center_x = (x0 + x1) / 2
    center_y = (top + bottom) / 2
    candidates = [
        rect for rect in page.rects
        if rect.get("fill")
        and rect["x0"] <= center_x <= rect["x1"]
        and rect["top"] <= center_y <= rect["bottom"]
    ]
    if not candidates:
        return None
    fill = color_hex(min(
        candidates,
        key=lambda rect: (rect["x1"] - rect["x0"]) * (rect["bottom"] - rect["top"]),
    ).get("non_stroking_color"))
    return PatternFill("solid", fgColor=fill) if fill else None


def table_column_edges(table):
    edges = sorted({
        round(edge, 3)
        for row in table.rows
        for bbox in row.cells if bbox
        for edge in (bbox[0], bbox[2])
    })
    expected = len(table.rows[0].cells) + 1 if table.rows else 0
    return edges if len(edges) == expected else []


def write_rows(sheet, rows, page=None, table=None):
    column_edges = table_column_edges(table) if table else []

    for row_index, row in enumerate(rows, start=1):
        for column_index, value in enumerate(row, start=1):
            cell = sheet.cell(row=row_index, column=column_index, value=excel_value(value))
            cell.border = TABLE_BORDER
            cell.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)
            if page and table and row_index <= len(table.rows):
                bbox = table.rows[row_index - 1].cells[column_index - 1]
                if bbox:
                    cell.font = cell_font(page, bbox)
                    fill = cell_fill(page, bbox)
                    if fill:
                        cell.fill = fill

    if table and column_edges:
        for column_index, (left, right) in enumerate(zip(column_edges, column_edges[1:]), start=1):
            sheet.column_dimensions[get_column_letter(column_index)].width = max(4, min(60, (right - left) * 0.19))

        for row_index, table_row in enumerate(table.rows, start=1):
            sheet.row_dimensions[row_index].height = max(6, table_row.bbox[3] - table_row.bbox[1])
            column_index = 0
            while column_index < len(table_row.cells):
                bbox = table_row.cells[column_index]
                if not bbox:
                    column_index += 1
                    continue
                right_edge = min(range(len(column_edges)), key=lambda edge: abs(column_edges[edge] - bbox[2]))
                end_column = max(column_index + 1, right_edge)
                if end_column > column_index + 1:
                    sheet.merge_cells(
                        start_row=row_index,
                        start_column=column_index + 1,
                        end_row=row_index,
                        end_column=end_column,
                    )
                column_index = end_column
        sheet.freeze_panes = "A2"
        sheet.sheet_view.showGridLines = False
        sheet.page_setup.orientation = "landscape"
        sheet.page_setup.fitToWidth = 1
        sheet.page_setup.fitToHeight = 0
        sheet.sheet_properties.pageSetUpPr.fitToPage = True
        return

    for column_index in range(1, (max((len(row) for row in rows), default=0) + 1)):
        width = min(
            80,
            max(len(str(sheet.cell(row=r, column=column_index).value or "")) for r in range(1, len(rows) + 1)) + 2,
        )
        sheet.column_dimensions[get_column_letter(column_index)].width = max(width, 12)

    sheet.sheet_view.showGridLines = False


def coordinate_table(page):
    words = page.extract_words(x_tolerance=1, y_tolerance=2) or []
    if not words:
        return []

    rows_by_y = {}
    for word in words:
        top_key = round(word["top"], 1)
        row_top = min(rows_by_y.keys(), key=lambda existing: abs(existing - top_key)) if rows_by_y else None
        if row_top is not None and abs(row_top - top_key) <= 4:
            rows_by_y[row_top].append(word)
        else:
            rows_by_y[top_key] = [word]

    ordered_rows = [sorted(row_words, key=lambda word: word["x0"]) for _, row_words in sorted(rows_by_y.items(), key=lambda item: item[0])]
    if not ordered_rows:
        return []

    header_words = ordered_rows[0]
    if len(header_words) < 2:
        return []

    headers = []
    header_starts = []
    for word in header_words:
        text = word["text"].strip()
        if text:
            headers.append(text)
            header_starts.append(word["x0"])

    headers = headers
    result = [headers]

    for words_in_row in ordered_rows[1:]:
        cells = [""] * len(headers)
        for word in words_in_row:
            x0 = word["x0"]
            column = len(header_starts) - 1
            for index, start in enumerate(header_starts):
                if x0 < start:
                    column = max(0, index - 1)
                    break
            text = word["text"].strip()
            if text and cells[column]:
                cells[column] += " " + text
            elif text:
                cells[column] = text
        if any(cells):
            result.append(cells)

    return result


def main():
    if len(sys.argv) != 3:
        print("Usage: pdf_to_excel.py <input.pdf> <output.xlsx>")
        return 1

    input_path = Path(sys.argv[1])
    output_path = Path(sys.argv[2])

    workbook = Workbook()
    workbook.remove(workbook.active)
    table_count = 0
    text_count = 0

    with pdfplumber.open(str(input_path)) as pdf:
        for page_number, page in enumerate(pdf.pages, start=1):
            detected_tables = page.find_tables() or []
            tables = [(table, table.extract()) for table in detected_tables]
            if tables:
                for table_index, (table_object, table_rows) in enumerate(tables, start=1):
                    table_count += 1
                    title = "Table {} (Page {})".format(table_index, page_number)
                    sheet = workbook.create_sheet(title[:31])
                    write_rows(sheet, table_rows, page, table_object)
            else:
                coordinate_rows = coordinate_table(page)
                if len(coordinate_rows) >= 2:
                    table_count += 1
                    sheet = workbook.create_sheet("Table (Page {})".format(page_number)[:31])
                    write_rows(sheet, coordinate_rows)
                else:
                    text = page.extract_text() or ""
                    if not text.strip():
                        continue
                    text_count += 1
                    sheet = workbook.create_sheet("Page {}".format(page_number)[:31])
                    write_rows(sheet, [[line.strip()] for line in text.splitlines() if line.strip()])

    if not table_count and not text_count:
        print("The PDF contains no extractable tables or text.")
        return 2

    output_path.parent.mkdir(parents=True, exist_ok=True)
    workbook.save(str(output_path))
    print(output_path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
