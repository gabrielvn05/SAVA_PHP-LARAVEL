#!/usr/bin/env python3
"""Normaliza placeholders en plantillas DOCX de oficios SAVA."""
from __future__ import annotations

import re
import shutil
import zipfile
from io import BytesIO
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = Path(r"c:\Users\gabri\Downloads")
OUT = ROOT / "resources" / "templates" / "oficios"

FILES = {
    "falta_marcado": "Oficio por reconocimiento Sava.docx",
    "calamidad_domestica": "Oficio por calamidad Sava.docx",
    "enfermedad": "Oficio medico Sava.docx",
    "viaje": "Oficio por viaje Sava.docx",
}

OUT_NAMES = {
    "falta_marcado": "reconocimiento.docx",
    "calamidad_domestica": "calamidad.docx",
    "enfermedad": "medico.docx",
    "viaje": "viaje.docx",
}

LINE_REPLACEMENTS = {
    "falta_marcado": [
        ("Fecha del incidente:", "Fecha del incidente: [Fecha del incidente]"),
        ("Tipo de marcación omitida/fallida:", "Tipo de marcación omitida/fallida: [Tipo de marcación omitida/fallida]"),
        ("Hora real de ingreso:", "Hora real de ingreso: [Hora real de ingreso]"),
        ("Hora real de salida:", "Hora real de salida: [Hora real de salida]"),
        ("Motivo de la falta de registro;", "Motivo de la falta de registro: [Motivo de la falta de registro]"),
        ("Descripción complementaria (opcional):", "Descripción complementaria (opcional): [Descripción complementaria]"),
    ],
    "calamidad_domestica": [
        ("Tipo de calamidad:", "Tipo de calamidad: [Tipo de calamidad]"),
        ("Nombre completo del familiar afectado:", "Nombre completo del familiar afectado: [Nombre completo del familiar afectado]"),
        ("Parentesco (hasta segundo grado de consanguinidad o afinidad):", "Parentesco (hasta segundo grado de consanguinidad o afinidad): [Parentesco]"),
        ("Descripción del hecho:", "Descripción del hecho: [Descripción del hecho]"),
        ("Lugar donde ocurrió:", "Lugar donde ocurrió: [Lugar donde ocurrió]"),
        ("Fecha del hecho:", "Fecha del hecho: [Fecha del hecho]"),
    ],
    "viaje": [
        ("Tipo de viaje:", "Tipo de viaje: [Tipo de viaje]"),
        ("Nombre del evento o institución de estudio:", "Nombre del evento o institución de estudio: [Nombre del evento o institución de estudio]"),
        ("Lugar (ciudad, país):", "Lugar (ciudad, país): [Lugar (ciudad, país)]"),
        (
            "Fechas del evento o actividad: desde el ____ hasta el ____ de ______ de _______",
            "Fechas del evento o actividad: [Fechas del evento o actividad]",
        ),
        ("Rol específico (si aplica):", "Rol específico (si aplica): [Rol específico]"),
        ("Breve descripción del objetivo académico:", "Breve descripción del objetivo académico: [Objetivo académico]"),
    ],
}


def normalize_oficio_codigo(xml: str) -> str:
    patterns = [
        r"OFICIO N° FACIVITEC-MMAAAA-NNNN",
        r"OFICIO N° FACIVITEC-032026-0005",
        r"<w:t[^>]*>OFICIO N° F</w:t></w:r><w:r[^>]*>.*?<w:t>ACIVITEC</w:t></w:r><w:r[^>]*>.*?<w:t>-MMAAAA-NNNN</w:t>",
        r"<w:t[^>]*>OFICIO N° F</w:t></w:r><w:r[^>]*>.*?<w:t>ACIVITEC</w:t></w:r><w:r[^>]*>.*?<w:t>-032026-0005</w:t>",
    ]
    for pattern in patterns:
        xml = re.sub(pattern, "OFICIO N° [Código del oficio]", xml, count=1, flags=re.DOTALL)
    return xml


def patch_xml(xml: str, key: str) -> str:
    xml = normalize_oficio_codigo(xml)

    # Reparar [Carrera] partido en plantilla médica.
    broken_carrera = (
        r"\[<\/w:t><\/w:r><w:r[^>]*><w:rPr>.*?<\/w:rPr><w:t>Carrera<\/w:t><\/w:r><w:r[^>]*><w:rPr>.*?<\/w:rPr><w:t xml:space=\"preserve\">\]"
    )
    xml = re.sub(broken_carrera, "[Carrera]", xml, flags=re.DOTALL)

    for old, new in LINE_REPLACEMENTS.get(key, []):
        xml = xml.replace(old, new)

    xml = xml.replace("Ángel Cristian Mera Macias", "[Nombre del decano]")

    return xml


def patch_docx(src: Path, dest: Path, key: str) -> None:
    raw = src.read_bytes()
    out_buf = BytesIO()
    with zipfile.ZipFile(BytesIO(raw), "r") as zin, zipfile.ZipFile(out_buf, "w", zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            data = zin.read(item.filename)
            if item.filename.endswith(".xml"):
                text = data.decode("utf-8")
                text = patch_xml(text, key)
                data = text.encode("utf-8")
            zout.writestr(item, data)
    dest.parent.mkdir(parents=True, exist_ok=True)
    dest.write_bytes(out_buf.getvalue())


def main() -> None:
    from tighten_oficio_firma import patch_docx as tighten_docx

    for key, filename in FILES.items():
        src = SRC / filename
        if not src.exists():
            src = ROOT / "storage" / "app" / "templates" / "oficios" / filename.replace(" ", "_")
        dest = OUT / OUT_NAMES[key]
        patch_docx(src, dest, key)
        tighten_docx(dest)
        print("OK", dest)


if __name__ == "__main__":
    main()
