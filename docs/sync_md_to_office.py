#!/usr/bin/env python3
"""
Sincroniza los documentos .md (fuente de verdad) hacia sus versiones .docx / .xlsx.

Regla del proyecto: si se edita un .md, correr este script para propagar
el cambio a Word y Excel. Nunca editar el .docx/.xlsx directamente.

Uso:
    python3 sync_md_to_office.py

Entradas (en md_source/):
    - glosario_datos.md      -> Glosario_Datos_Barberia_v1.docx
    - product_backlog.md     -> Backlog_Barberia_v1.xlsx (hoja "Product Backlog")
    - sprint_1_backlog.md    -> Backlog_Barberia_v1.xlsx (hoja "Sprint 1 Backlog")
"""

import re
import sys
from pathlib import Path

from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

BASE = Path(__file__).parent
SRC = BASE / "md_source"
OUT = BASE

NAVY = "1F3864"
ACCENT = "2E5395"
LIGHTGRAY = "F2F2F2"
WHITE = "FFFFFF"

# ============================================================
# ---------- Helpers: docx cell shading ----------
# ============================================================

def shade_cell(cell, hex_color):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_color)
    tcPr.append(shd)


def set_cell_text(cell, text, bold=False, color=None, size=10, italic=False):
    cell.text = ""
    p = cell.paragraphs[0]
    run = p.add_run(text)
    run.bold = bold
    run.italic = italic
    run.font.size = Pt(size)
    if color:
        run.font.color.rgb = RGBColor.from_string(color)


# ============================================================
# ---------- 1. Parse glosario_datos.md -> docx ----------
# ============================================================

def parse_glosario(md_text):
    """Returns dict: {title, intro_paragraphs, entities: [{name, purpose, relations, fields:[(campo,tipo,desc)]}], closing_bullets}"""
    lines = md_text.splitlines()
    entities = []
    current = None
    intro = []
    closing = []
    section = "intro"  # intro -> entities -> closing

    i = 0
    while i < len(lines):
        line = lines[i]

        if line.startswith("## 2. Entidades"):
            section = "entities"
            i += 1
            continue
        if line.startswith("## 3. Decisiones"):
            section = "closing"
            i += 1
            continue

        if section == "intro":
            if line.startswith("## 1. Propósito") or line.startswith("**Versión") or line.startswith("**Complementa") or line.startswith("**Fuente"):
                i += 1
                continue
            if line.strip() and not line.startswith("#"):
                intro.append(line.strip())

        elif section == "entities":
            m = re.match(r"^### (.+)$", line)
            if m:
                if current:
                    entities.append(current)
                current = {"name": m.group(1).strip(), "purpose": "", "relations": "", "fields": []}
                i += 1
                continue
            if current is not None:
                rel_m = re.match(r"^\*\*Relaciones clave:\*\*\s*(.+)$", line)
                if rel_m:
                    current["relations"] = rel_m.group(1).strip()
                    i += 1
                    continue
                field_m = re.match(r"^\|\s*`(.+?)`\s*\|\s*(.+?)\s*\|\s*(.+?)\s*\|$", line)
                if field_m:
                    current["fields"].append((field_m.group(1), field_m.group(2), field_m.group(3)))
                    i += 1
                    continue
                if line.startswith("| Campo") or line.startswith("|---"):
                    i += 1
                    continue
                if line.strip() == "---" or line.strip() == "":
                    i += 1
                    continue
                if not line.startswith("#") and line.strip():
                    current["purpose"] += (" " if current["purpose"] else "") + line.strip()

        elif section == "closing":
            bm = re.match(r"^-\s+(.+)$", line)
            if bm:
                closing.append(bm.group(1).strip())

        i += 1

    if current:
        entities.append(current)

    return {"intro": intro, "entities": entities, "closing": closing}


def build_glosario_docx(data, out_path):
    doc = Document()

    # base font
    style = doc.styles["Normal"]
    style.font.name = "Calibri"
    style.font.size = Pt(11)

    for section in doc.sections:
        section.top_margin = Cm(2)
        section.bottom_margin = Cm(2)
        section.left_margin = Cm(2)
        section.right_margin = Cm(2)

    title = doc.add_paragraph()
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = title.add_run("GLOSARIO DE DATOS")
    r.bold = True
    r.font.size = Pt(26)
    r.font.color.rgb = RGBColor.from_string(NAVY)

    sub = doc.add_paragraph()
    sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = sub.add_run("Sistema de Gestión de Turnos — Barbería")
    r.font.size = Pt(15)
    r.font.color.rgb = RGBColor.from_string(ACCENT)

    ver = doc.add_paragraph()
    ver.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = ver.add_run("Versión 1.0 — Complementa el Diagrama Entidad-Relación (DER)")
    r.italic = True
    r.font.size = Pt(10)
    r.font.color.rgb = RGBColor.from_string("666666")

    h1 = doc.add_heading("1. Propósito de este documento", level=1)
    for run in h1.runs:
        run.font.color.rgb = RGBColor.from_string(NAVY)
    for para in data["intro"]:
        doc.add_paragraph(para)

    h1b = doc.add_heading("2. Entidades del modelo", level=1)
    for run in h1b.runs:
        run.font.color.rgb = RGBColor.from_string(NAVY)

    for ent in data["entities"]:
        h2 = doc.add_heading(ent["name"], level=2)
        for run in h2.runs:
            run.font.color.rgb = RGBColor.from_string(ACCENT)

        doc.add_paragraph(ent["purpose"])

        if ent["relations"]:
            p = doc.add_paragraph()
            r1 = p.add_run("Relaciones clave: ")
            r1.bold = True
            r1.italic = True
            r1.font.color.rgb = RGBColor.from_string("6B7280")
            r2 = p.add_run(ent["relations"])
            r2.italic = True
            r2.font.color.rgb = RGBColor.from_string("6B7280")

        if ent["fields"]:
            table = doc.add_table(rows=1, cols=3)
            table.style = "Table Grid"
            hdr = table.rows[0].cells
            for idx, htext in enumerate(["Campo", "Tipo", "Descripción"]):
                set_cell_text(hdr[idx], htext, bold=True, color=WHITE, size=10)
                shade_cell(hdr[idx], NAVY)
            for i, (campo, tipo, desc) in enumerate(ent["fields"]):
                row = table.add_row().cells
                bg = "FFFFFF" if i % 2 == 0 else LIGHTGRAY
                set_cell_text(row[0], campo, bold=True, color=ACCENT, size=10)
                shade_cell(row[0], bg)
                set_cell_text(row[1], tipo, italic=True, size=10)
                shade_cell(row[1], bg)
                set_cell_text(row[2], desc, size=10)
                shade_cell(row[2], bg)

        doc.add_paragraph()

    h1c = doc.add_heading("3. Decisiones de modelado a tener en cuenta", level=1)
    for run in h1c.runs:
        run.font.color.rgb = RGBColor.from_string(NAVY)
    for b in data["closing"]:
        doc.add_paragraph(b, style="List Bullet")

    doc.save(str(out_path))


# ============================================================
# ---------- 2. Parse product_backlog.md -> excel sheet ----------
# ============================================================

def parse_product_backlog(md_text):
    lines = md_text.splitlines()
    hus = []
    current = None
    section_module = None
    mode = None  # 'ac' collecting acceptance criteria

    for line in lines:
        mod_m = re.match(r"^## Módulo de .+\((\w+)\)$", line)
        if mod_m:
            section_module = mod_m.group(1)
            continue

        hu_m = re.match(r"^### (HU-\w+-\d+) — (.+)$", line)
        if hu_m:
            if current:
                hus.append(current)
            current = {
                "id": hu_m.group(1),
                "modulo": section_module,
                "titulo": hu_m.group(2).strip(),
                "prioridad": "",
                "sprint": "",
                "adr": "-",
                "rol": "",
                "quiero": "",
                "para": "",
                "criterios": [],
                "nota": "",
            }
            mode = None
            continue

        if current is None:
            continue

        meta_m = re.match(r"^- \*\*Prioridad:\*\*\s*(\w+)\s*·\s*\*\*Sprint sugerido:\*\*\s*(\d+)\s*·\s*\*\*ADR relacionado:\*\*\s*(.+)$", line)
        if meta_m:
            current["prioridad"] = meta_m.group(1).strip()
            current["sprint"] = meta_m.group(2).strip()
            current["adr"] = meta_m.group(3).strip()
            continue

        narr_m = re.match(r"^- \*\*Como\*\*\s*(.+?),\s*\*\*quiero\*\*\s*(.+?),\s*\*\*para\*\*\s*(.+?)\.$", line)
        if narr_m:
            current["rol"] = narr_m.group(1).strip()
            current["quiero"] = narr_m.group(2).strip()
            current["para"] = narr_m.group(3).strip()
            continue

        if line.strip() == "- **Criterios de aceptación:**":
            mode = "ac"
            continue

        note_m = re.match(r"^- \*\*Nota:\*\*\s*(.+)$", line)
        if note_m:
            current["nota"] = note_m.group(1).strip()
            mode = None
            continue

        if mode == "ac":
            ac_m = re.match(r"^\s+-\s+(.+)$", line)
            if ac_m:
                current["criterios"].append(ac_m.group(1).strip())
                continue
            else:
                mode = None

    if current:
        hus.append(current)

    return hus


def parse_sprint1(md_text):
    lines = md_text.splitlines()
    rows = []
    in_table = False
    for line in lines:
        if line.startswith("| Orden"):
            in_table = True
            continue
        if line.startswith("|---"):
            continue
        if in_table:
            if not line.startswith("|"):
                break
            cells = [c.strip() for c in line.strip().strip("|").split("|")]
            if len(cells) == 6:
                rows.append({
                    "orden": cells[0],
                    "id": cells[1],
                    "modulo": cells[2],
                    "titulo": cells[3],
                    "prioridad": cells[4],
                    "razon": cells[5],
                })
    return rows


def build_excel(hus, sprint1_rows, out_path):
    FONT_NAME = "Calibri"
    header_font = Font(name=FONT_NAME, bold=True, color=WHITE, size=11)
    header_fill = PatternFill(start_color=NAVY, end_color=NAVY, fill_type="solid")
    title_font = Font(name=FONT_NAME, bold=True, color=NAVY, size=16)
    subtitle_font = Font(name=FONT_NAME, italic=True, color="666666", size=10)
    normal_font = Font(name=FONT_NAME, size=10)
    bold_font = Font(name=FONT_NAME, bold=True, size=10)
    wrap_align = Alignment(wrap_text=True, vertical="top", horizontal="left")
    center_align = Alignment(wrap_text=True, vertical="center", horizontal="center")

    thin_border = Border(
        left=Side(style="thin", color="D0D5DE"),
        right=Side(style="thin", color="D0D5DE"),
        top=Side(style="thin", color="D0D5DE"),
        bottom=Side(style="thin", color="D0D5DE"),
    )

    prio_fill = {
        "Must": PatternFill(start_color="C6E8CB", end_color="C6E8CB", fill_type="solid"),
        "Should": PatternFill(start_color="C9DCF5", end_color="C9DCF5", fill_type="solid"),
        "Could": PatternFill(start_color="F7E6BE", end_color="F7E6BE", fill_type="solid"),
    }
    prio_font = {
        "Must": Font(name=FONT_NAME, bold=True, color="1B5E20", size=10),
        "Should": Font(name=FONT_NAME, bold=True, color="0D3C7A", size=10),
        "Could": Font(name=FONT_NAME, bold=True, color="7A5209", size=10),
    }
    modulo_fill = {
        "TUR": PatternFill(start_color="1F3864", end_color="1F3864", fill_type="solid"),
        "CLI": PatternFill(start_color="2E7D32", end_color="2E7D32", fill_type="solid"),
        "SER": PatternFill(start_color="B8791A", end_color="B8791A", fill_type="solid"),
        "SEG": PatternFill(start_color="8A8F9B", end_color="8A8F9B", fill_type="solid"),
    }

    wb = openpyxl.Workbook()

    # ---- Sheet 1: Product Backlog ----
    ws1 = wb.active
    ws1.title = "Product Backlog"

    ws1.merge_cells("A1:J1")
    ws1["A1"] = "PRODUCT BACKLOG — Sistema de Gestión de Turnos (Barbería)"
    ws1["A1"].font = title_font
    ws1.merge_cells("A2:J2")
    ws1["A2"] = "Versión 1.0 — Basado en Documento de Alcance v2, Decisiones de Arquitectura v1 y DER v1"
    ws1["A2"].font = subtitle_font

    headers1 = ["ID", "Módulo", "Título", "Como (rol)", "Quiero (acción)", "Para (beneficio)",
                "Criterios de aceptación", "Prioridad (MoSCoW)", "ADR relacionado", "Sprint sugerido"]
    row0 = 4
    for col, h in enumerate(headers1, start=1):
        c = ws1.cell(row=row0, column=col, value=h)
        c.font = header_font
        c.fill = header_fill
        c.alignment = center_align
        c.border = thin_border

    r = row0 + 1
    for hu in hus:
        criterios_text = "\n".join(hu["criterios"])
        values = [hu["id"], hu["modulo"], hu["titulo"], hu["rol"], hu["quiero"], hu["para"],
                  criterios_text, hu["prioridad"], hu["adr"], hu["sprint"]]
        for col, v in enumerate(values, start=1):
            c = ws1.cell(row=r, column=col, value=v)
            c.font = normal_font
            c.alignment = wrap_align
            c.border = thin_border
            if col == 1:
                c.font = bold_font
            if col == 2:
                c.fill = modulo_fill.get(hu["modulo"], PatternFill())
                c.font = Font(name=FONT_NAME, bold=True, color=WHITE, size=10)
                c.alignment = center_align
            if col == 8:
                c.fill = prio_fill.get(hu["prioridad"], PatternFill())
                c.font = prio_font.get(hu["prioridad"], normal_font)
                c.alignment = center_align
            if col == 10:
                c.alignment = center_align
        lines = max(1, criterios_text.count("\n") + 1)
        ws1.row_dimensions[r].height = max(26, lines * 18 + 8)
        r += 1

    widths1 = [12, 10, 32, 16, 34, 30, 55, 14, 13, 12]
    for i, w in enumerate(widths1, start=1):
        ws1.column_dimensions[get_column_letter(i)].width = w

    ws1.freeze_panes = "A5"
    ws1.auto_filter.ref = f"A{row0}:J{r-1}"
    ws1.page_setup.orientation = "landscape"
    ws1.page_setup.fitToWidth = 1
    ws1.page_setup.fitToHeight = 0
    ws1.sheet_properties.pageSetUpPr.fitToPage = True

    # ---- Sheet 2: Sprint 1 Backlog ----
    ws2 = wb.create_sheet("Sprint 1 Backlog")

    ws2.merge_cells("A1:F1")
    ws2["A1"] = "SPRINT 1 — Núcleo Mínimo Viable"
    ws2["A1"].font = title_font
    ws2.merge_cells("A2:F2")
    ws2["A2"] = "Orden de desarrollo sugerido según dependencias técnicas y de negocio"
    ws2["A2"].font = subtitle_font

    headers2 = ["Orden", "ID", "Módulo", "Título", "Prioridad", "Por qué va en este orden"]
    row0b = 4
    for col, h in enumerate(headers2, start=1):
        c = ws2.cell(row=row0b, column=col, value=h)
        c.font = header_font
        c.fill = header_fill
        c.alignment = center_align
        c.border = thin_border

    r2 = row0b + 1
    for row in sprint1_rows:
        values = [int(row["orden"]), row["id"], row["modulo"], row["titulo"], row["prioridad"], row["razon"]]
        for col, v in enumerate(values, start=1):
            c = ws2.cell(row=r2, column=col, value=v)
            c.font = normal_font
            c.alignment = wrap_align
            c.border = thin_border
            if col == 1:
                c.font = Font(name=FONT_NAME, bold=True, size=12, color=NAVY)
                c.alignment = center_align
            if col == 2:
                c.font = bold_font
            if col == 3:
                c.fill = modulo_fill.get(row["modulo"], PatternFill())
                c.font = Font(name=FONT_NAME, bold=True, color=WHITE, size=10)
                c.alignment = center_align
            if col == 5:
                c.fill = prio_fill.get(row["prioridad"], PatternFill())
                c.font = prio_font.get(row["prioridad"], normal_font)
                c.alignment = center_align
        ws2.row_dimensions[r2].height = 34
        r2 += 1

    widths2 = [8, 12, 10, 34, 12, 55]
    for i, w in enumerate(widths2, start=1):
        ws2.column_dimensions[get_column_letter(i)].width = w

    ws2.freeze_panes = "A5"
    ws2.page_setup.orientation = "landscape"
    ws2.page_setup.fitToWidth = 1
    ws2.page_setup.fitToHeight = 0
    ws2.sheet_properties.pageSetUpPr.fitToPage = True

    note_row = r2 + 1
    ws2.merge_cells(f"A{note_row}:F{note_row}")
    ws2[f"A{note_row}"] = ("Nota: el resto de las Historias de Usuario (cancelación, no-show, WhatsApp, "
                            "fidelización, combos, stock, reportes) se planifican para Sprint 2 en adelante "
                            "— ver columna 'Sprint sugerido' en la hoja Product Backlog.")
    ws2[f"A{note_row}"].font = Font(name=FONT_NAME, italic=True, size=9, color="6B7280")
    ws2[f"A{note_row}"].alignment = wrap_align
    ws2.row_dimensions[note_row].height = 30

    wb.save(str(out_path))


# ============================================================
# ---------- main ----------
# ============================================================

def main():
    glosario_md = (SRC / "glosario_datos.md").read_text(encoding="utf-8")
    backlog_md = (SRC / "product_backlog.md").read_text(encoding="utf-8")
    sprint1_md = (SRC / "sprint_1_backlog.md").read_text(encoding="utf-8")

    print("Parseando glosario_datos.md...")
    glosario_data = parse_glosario(glosario_md)
    print(f"  -> {len(glosario_data['entities'])} entidades encontradas")
    build_glosario_docx(glosario_data, OUT / "Glosario_Datos_Barberia_v1.docx")
    print("  -> Glosario_Datos_Barberia_v1.docx regenerado")

    print("Parseando product_backlog.md...")
    hus = parse_product_backlog(backlog_md)
    print(f"  -> {len(hus)} historias de usuario encontradas")

    print("Parseando sprint_1_backlog.md...")
    sprint1_rows = parse_sprint1(sprint1_md)
    print(f"  -> {len(sprint1_rows)} HU en el Sprint 1")

    build_excel(hus, sprint1_rows, OUT / "Backlog_Barberia_v1.xlsx")
    print("  -> Backlog_Barberia_v1.xlsx regenerado (2 hojas)")

    print("\nSincronización completa.")


if __name__ == "__main__":
    main()
