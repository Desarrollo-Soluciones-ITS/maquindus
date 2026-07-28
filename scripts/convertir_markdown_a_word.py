#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Convierte docs/MANUAL_DE_USUARIO.md a un documento Word (.docx)
con formato profesional, listo para añadir imágenes.
"""

import re
import os
from docx import Document
from docx.shared import Inches, Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.style import WD_STYLE_TYPE
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

def add_heading_styled(doc, text, level=1):
    """Añade un encabezado con formato."""
    heading = doc.add_heading(text, level=level)
    for run in heading.runs:
        run.font.color.rgb = RGBColor(0, 51, 102)  # Azul oscuro
    return heading

def add_table_from_text(doc, table_text):
    """Convierte texto de tabla markdown a tabla de Word."""
    lines = table_text.strip().split('\n')
    rows = []
    for line in lines:
        line = line.strip()
        if not line or line.startswith('|---') or line.startswith('├'):
            continue
        # Limpiar caracteres de borde de tabla ASCII
        line = line.replace('│', '|').replace('┌', '').replace('┐', '') \
                   .replace('└', '').replace('┘', '') \
                   .replace('├', '').replace('┤', '') \
                   .replace('─', '').replace('┬', '').replace('┴', '') \
                   .strip()
        if line.startswith('|') and line.endswith('|'):
            cells = [c.strip() for c in line.split('|')[1:-1]]
            rows.append(cells)
    
    if len(rows) < 2:
        return None
    
    table = doc.add_table(rows=len(rows), cols=len(rows[0]))
    table.style = 'Light Grid Accent 1'
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    for i, row_cells in enumerate(rows):
        for j, cell_text in enumerate(row_cells):
            cell = table.rows[i].cells[j]
            cell.text = cell_text
            for paragraph in cell.paragraphs:
                paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
                for run in paragraph.runs:
                    run.font.size = Pt(10)
                    if i == 0:  # Header row
                        run.bold = True
    
    doc.add_paragraph()  # Espacio después de tabla
    return table

def add_code_block(doc, code_text):
    """Añade un bloque de código con formato."""
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(1)
    run = p.add_run(code_text)
    run.font.name = 'Courier New'
    run.font.size = Pt(9)
    run.font.color.rgb = RGBColor(50, 50, 50)
    # Fondo gris claro no es fácil en python-docx, usamos cursiva como alternativa
    return p

def convert_markdown_to_docx(md_path, docx_path):
    """Convierte un archivo Markdown a Word."""
    with open(md_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    doc = Document()
    
    # Configurar estilos por defecto
    style = doc.styles['Normal']
    font = style.font
    font.name = 'Calibri'
    font.size = Pt(11)
    
    # Espaciado
    style.paragraph_format.space_after = Pt(6)
    style.paragraph_format.line_spacing = 1.15
    
    # Título principal
    title = doc.add_heading('Manual de Usuario', level=0)
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    for run in title.runs:
        run.font.color.rgb = RGBColor(0, 51, 102)
    
    subtitle = doc.add_paragraph('Sistema de Gestión Documental Maquindus')
    subtitle.alignment = WD_ALIGN_PARAGRAPH.CENTER
    for run in subtitle.runs:
        run.font.size = Pt(16)
        run.font.color.rgb = RGBColor(100, 100, 100)
    
    doc.add_paragraph()  # Espacio
    
    # Procesar línea por línea
    lines = content.split('\n')
    i = 0
    in_code_block = False
    code_buffer = []
    in_table = False
    table_buffer = []
    
    while i < len(lines):
        line = lines[i]
        
        # Bloques de código (```)
        if line.strip().startswith('```'):
            if in_code_block:
                add_code_block(doc, '\n'.join(code_buffer))
                code_buffer = []
                in_code_block = False
            else:
                in_code_block = True
            i += 1
            continue
        
        if in_code_block:
            code_buffer.append(line)
            i += 1
            continue
        
        # Detectar tablas ASCII (con ┌ ┐ └ ┘ ├ ┤ ┬ ┴ │ ─)
        if re.match(r'^[┌├└]', line) or re.match(r'^\s*[┌├└]', line):
            table_buffer.append(line)
            in_table = True
            i += 1
            continue
        
        if in_table:
            if re.match(r'^[└]', line):
                table_buffer.append(line)
                add_table_from_text(doc, '\n'.join(table_buffer))
                table_buffer = []
                in_table = False
            else:
                table_buffer.append(line)
            i += 1
            continue
        
        # Encabezados
        if line.startswith('## '):
            add_heading_styled(doc, line[3:].strip(), level=2)
        elif line.startswith('### '):
            add_heading_styled(doc, line[4:].strip(), level=3)
        elif line.startswith('#### '):
            add_heading_styled(doc, line[5:].strip(), level=4)
        elif line.startswith('# '):
            # Saltar título principal (ya lo pusimos)
            pass
        elif line.startswith('---') or line.startswith('***'):
            doc.add_paragraph('_' * 60)
        elif line.strip() == '':
            doc.add_paragraph()
        elif line.startswith('- '):
            p = doc.add_paragraph(line[2:].strip(), style='List Bullet')
        elif line.startswith('* ') or line.startswith('  * '):
            p = doc.add_paragraph(line.strip()[2:].strip(), style='List Bullet')
        elif '|' in line and line.strip().startswith('|'):
            # Tabla markdown simple
            pass
        else:
            # Párrafo normal
            p = doc.add_paragraph(line.strip())
        
        i += 1
    
    # Guardar
    doc.save(docx_path)
    print(f"Documento Word creado: {docx_path}")

if __name__ == '__main__':
    md_file = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'docs', 'MANUAL_DE_USUARIO.md')
    docx_file = os.path.join(os.path.dirname(md_file), 'MANUAL_DE_USUARIO.docx')
    
    if os.path.exists(md_file):
        convert_markdown_to_docx(md_file, docx_file)
        print("Conversión completada exitosamente!")
    else:
        print(f"Error: No se encuentra el archivo {md_file}")