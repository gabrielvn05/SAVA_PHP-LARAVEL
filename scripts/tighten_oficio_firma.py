#!/usr/bin/env python3
"""Reduce espacio en tabla de firmas sin borrar contenido."""
from __future__ import annotations

import re
import zipfile
from io import BytesIO
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TEMPLATES = ROOT / "resources" / "templates" / "oficios"


def paragraph_is_empty(p_xml: str) -> bool:
    texts = re.findall(r"<w:t[^>]*>([^<]*)</w:t>", p_xml)
    if not texts:
        return True

    return all(not t.strip() for t in texts)


def tighten_xml(xml: str) -> str:
    xml = xml.replace("Fecha de generación del documento", "Generado el")
    xml = xml.replace("Fecha de generaci\u00f3n del documento", "Generado el")

    for height in ("1337", "1200", "1000", "900", "720"):
        xml = xml.replace(f'w:val="{height}"', 'w:val="480"')

    marker = "Atentamente,"
    pos = xml.find(marker)
    if pos < 0:
        return xml

    tail = xml[pos:]
    tbl_start = tail.find("<w:tbl>")
    if tbl_start < 0:
        return xml

    head = xml[: pos + tbl_start]
    tbl_and_rest = tail[tbl_start:]

    # Solo párrafos vacíos dentro de la tabla de firmas.
    tbl_end = tbl_and_rest.find("</w:tbl>")
    if tbl_end < 0:
        return xml

    tbl = tbl_and_rest[: tbl_end + len("</w:tbl>")]
    rest = tbl_and_rest[tbl_end + len("</w:tbl>") :]

    removed = 0
    while removed < 6:
        new_tbl, n = re.subn(
            r"<w:p\b[^>]*>.*?</w:p>",
            lambda m: "" if paragraph_is_empty(m.group(0)) and removed < 6 else m.group(0),
            tbl,
            count=1,
            flags=re.DOTALL,
        )
        if new_tbl == tbl:
            break
        if new_tbl != tbl:
            removed += 1
            tbl = new_tbl

    # cantSplit en la primera fila de la tabla (si no existe).
    if "<w:cantSplit/>" not in tbl[:2500]:
        tbl = tbl.replace(
            "<w:tr ",
            "<w:tr ",
            1,
        )
        idx = tbl.find("<w:tr ")
        if idx >= 0:
            close = tbl.find(">", idx)
            if close >= 0:
                tbl = tbl[: close + 1] + "<w:trPr><w:cantSplit/></w:trPr>" + tbl[close + 1 :]

    head = re.sub(
        r"(<w:spacing w:after=\")\d+(\")",
        r"\g<1>60\2",
        head,
        count=2,
    )

    return head + tbl + rest


def patch_docx(path: Path) -> None:
    raw = path.read_bytes()
    out = BytesIO()
    with zipfile.ZipFile(BytesIO(raw), "r") as zin, zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            data = zin.read(item.filename)
            if item.filename == "word/document.xml":
                text = tighten_xml(data.decode("utf-8"))
                data = text.encode("utf-8")
            zout.writestr(item, data)
    path.write_bytes(out.getvalue())
    print("OK", path.name)


def main() -> None:
    for docx in sorted(TEMPLATES.glob("*.docx")):
        patch_docx(docx)


if __name__ == "__main__":
    main()
