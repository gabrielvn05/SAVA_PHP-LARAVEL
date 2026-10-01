#!/usr/bin/env python3
"""
Genera plantillas SAVA en resources/templates/oficios/ desde los DOCX originales.
Ejecutar tras actualizar los archivos en Downloads.
"""
from __future__ import annotations

import re
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

LINE_REPLACEMENTS: dict[str, list[tuple[str, str]]] = {
    "falta_marcado": [
        ("Fecha del incidente:", "Fecha del incidente: [Fecha del incidente]"),
        ("Tipo de marcación omitida/fallida:", "Tipo de marcación omitida/fallida: [Tipo de marcación omitida/fallida]"),
        ("Hora real de ingreso:", "Hora real de ingreso: [Hora real de ingreso]"),
        ("Hora real de salida:", "Hora real de salida: [Hora real de salida]"),
        ("Motivo de la falta de registro;", "Motivo de la falta de registro: [Motivo de la falta de registro]"),
        ("Motivo de la falta de registro:", "Motivo de la falta de registro: [Motivo de la falta de registro]"),
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
        ("Rol específico (si aplica):", "Rol específico (si aplica): [Rol específico]"),
        ("Breve descripción del objetivo académico:", "Breve descripción del objetivo académico: [Objetivo académico]"),
    ],
}

FECHAS_VIAJE_PARAGRAPH = (
    '<w:p w14:paraId="FECHAS001" w14:textId="77777777" w:rsidR="00E61206" w:rsidRPr="00EC5A55" '
    'w:rsidRDefault="00E61206" w:rsidP="00EC5A55">'
    '<w:pPr><w:spacing w:line="240" w:lineRule="auto" w:after="60"/>'
    '<w:jc w:val="both"/>'
    '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
    '<w:lang w:val="es-EC"/></w:rPr></w:pPr>'
    '<w:r w:rsidRPr="00EC5A55"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" '
    'w:cs="Times New Roman"/><w:lang w:val="es-EC"/></w:rPr>'
    "<w:t>Fechas del evento o actividad: [Fechas del evento o actividad]</w:t></w:r></w:p>"
)


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


def fix_broken_carrera_medico(xml: str) -> str:
    broken = (
        r"\[<\/w:t><\/w:r><w:r[^>]*><w:rPr>.*?<\/w:rPr><w:t>Carrera<\/w:t><\/w:r><w:r[^>]*><w:rPr>.*?<\/w:rPr><w:t xml:space=\"preserve\">\]"
    )
    return re.sub(broken, "[Carrera]", xml, flags=re.DOTALL)


def normalize_viaje_fechas(xml: str) -> str:
    replacements = [
        "Fechas del evento o actividad: desde el [fecha inicio] hasta el [fecha fin] de [mes] de [año].",
        "Fechas del evento o actividad: desde el [fecha inicio] hasta el [fecha fin] de [mes] de [a\u00f1o].",
        "Fechas del evento o actividad: desde el ____ hasta el ____ de ______ de _______",
    ]
    for text in replacements:
        if text in xml:
            xml = xml.replace(
                text,
                "Fechas del evento o actividad: [Fechas del evento o actividad]",
            )

    xml = re.sub(
        r"<w:p\b[^>]*>(?:(?!</w:p>).)*Fechas del evento o actividad:(?:(?!</w:p>).)*</w:p>",
        FECHAS_VIAJE_PARAGRAPH,
        xml,
        count=1,
        flags=re.DOTALL,
    )
    return xml


def normalize_firma_table_jc(xml: str) -> str:
    """Evita justificación en la tabla de firma (rompe «Correo Institucional» en el visor)."""
    m = re.search(
        r"(<w:tbl\b[^>]*>(?:(?!</w:tbl>).)*Aprobado por:(?:(?!</w:tbl>).)*</w:tbl>)",
        xml,
        flags=re.DOTALL,
    )
    if not m:
        return xml
    table = m.group(1)
    fixed = table.replace('<w:jc w:val="both"/>', '<w:jc w:val="left"/>')
    fixed = fixed.replace('<w:jc w:val="distribute"/>', '<w:jc w:val="left"/>')
    return xml.replace(table, fixed, 1)


def patch_xml(xml: str, key: str) -> str:
    xml = normalize_oficio_codigo(xml)
    xml = fix_broken_carrera_medico(xml)

    if key == "viaje":
        xml = normalize_viaje_fechas(xml)

    for old, new in LINE_REPLACEMENTS.get(key, []):
        xml = xml.replace(old, new)

    xml = xml.replace("Ángel Cristian Mera Macias", "[Nombre del decano]")
    xml = normalize_firma_table_jc(xml)

    return xml


def patch_docx(src: Path, dest: Path, key: str) -> None:
    raw = src.read_bytes()
    out_buf = BytesIO()
    with zipfile.ZipFile(BytesIO(raw), "r") as zin, zipfile.ZipFile(out_buf, "w", zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            data = zin.read(item.filename)
            if item.filename.endswith(".xml"):
                text = patch_xml(data.decode("utf-8"), key)
                data = text.encode("utf-8")
            zout.writestr(item, data)
    dest.parent.mkdir(parents=True, exist_ok=True)
    dest.write_bytes(out_buf.getvalue())


def validate_docx(path: Path, key: str) -> list[str]:
    xml = zipfile.ZipFile(path).read("word/document.xml").decode("utf-8")
    errors: list[str] = []
    required = {
        "falta_marcado": ["[Código del oficio]", "[Fecha del incidente]", "Aprobado por", "[Correo]"],
        "calamidad_domestica": ["[Tipo de calamidad]", "[Fecha de inicio de la falta]", "Aprobado por"],
        "enfermedad": ["[Carrera]", "[Diagnóstico principal]", "Aprobado por"],
        "viaje": ["[Fechas del evento o actividad]", "[Tipo de viaje]", "Aprobado por"],
    }
    for token in required.get(key, []):
        if token not in xml:
            errors.append(f"Falta {token}")
    if "____" in xml:
        errors.append("Contiene guiones bajos sin reemplazar")
    if "[fecha inicio]" in xml or "[fecha fin]" in xml:
        errors.append("Placeholders viejos de fecha de evento")
    broken = re.search(r"\[<\/w:t>", xml)
    if broken:
        errors.append("Placeholder XML partido")
    return errors


def main() -> None:
    for key, filename in FILES.items():
        src = SRC / filename
        if not src.exists():
            raise FileNotFoundError(f"No se encontró {src}")
        dest = OUT / OUT_NAMES[key]
        patch_docx(src, dest, key)
        errors = validate_docx(dest, key)
        if errors:
            raise RuntimeError(f"{dest.name}: " + "; ".join(errors))
        print("OK", dest)


if __name__ == "__main__":
    main()
