import os
import json
from pathlib import Path

DOCX_PATH = r"C:\Users\sammy\Desktop\all sem courses.docx"
RESULT_PDFS = [
    r"C:\Users\sammy\Desktop\All students\PRE-COLLEGE RESULTS (HO BRANCH).pdf",
    r"C:\Users\sammy\Desktop\All students\FIRST SEMESTER RESULT (HO BRANCH).pdf",
    r"C:\Users\sammy\Desktop\All students\SECOND SEMESTER RESULTS (HO BRANCH).pdf",
]


def extract_courses_from_docx(path):
    try:
        from docx import Document
    except Exception as e:
        print("Missing python-docx. Install with: py -m pip install --user python-docx")
        raise

    doc = Document(path)
    courses = []

    # Try to extract from tables first
    for table in doc.tables:
        # assume header row then data rows
        headers = [c.text.strip().lower() for c in table.rows[0].cells]
        for row in table.rows[1:]:
            cells = [c.text.strip() for c in row.cells]
            if not any(cells):
                continue
            item = dict()
            for i, header in enumerate(headers):
                key = header.replace(' ', '_')
                item[key] = cells[i] if i < len(cells) else ''
            # normalize common fields
            course = {
                'code': item.get('code') or item.get('course_code') or item.get('course') or '',
                'title': item.get('title') or item.get('course_title') or item.get('name') or '',
                'credit': item.get('credit') or item.get('credits') or '',
                'semester': item.get('semester') or '',
                'program': item.get('program') or '',
                'raw': item,
            }
            courses.append(course)

    # Fallback: parse paragraphs for lines that look like CODE - Title
    if not courses:
        for p in doc.paragraphs:
            t = p.text.strip()
            if not t:
                continue
            # naive pattern: ABC123 - Course Title
            if '-' in t and any(ch.isdigit() for ch in t.split('-')[0]):
                parts = t.split('-', 1)
                code = parts[0].strip()
                title = parts[1].strip()
                courses.append({'code': code, 'title': title, 'credit': '', 'semester': '', 'program': '', 'raw': {'line': t}})

    return courses


def extract_results_from_pdfs(paths):
    try:
        import pdfplumber
    except Exception:
        print("Missing pdfplumber. Install with: py -m pip install --user pdfplumber")
        raise

    results = []

    for p in paths:
        if not os.path.exists(p):
            print(f"Warning: PDF not found: {p}")
            continue
        with pdfplumber.open(p) as pdf:
            for page in pdf.pages:
                # extract tables
                try:
                    tables = page.extract_tables()
                except Exception:
                    tables = []
                for table in tables:
                    if not table:
                        continue
                    # find header row
                    header = [c.strip().lower() if c else '' for c in table[0]]
                    # heuristics: look for 'student', 'id', 'course', 'code', 'exam'
                    for row in table[1:]:
                        rowd = {header[i]: (row[i] or '').strip() if i < len(row) else '' for i in range(len(header))}
                        # try to extract student id/email and course scores
                        entry = {
                            'source_pdf': os.path.basename(p),
                            'student_identifier': rowd.get('student id') or rowd.get('id') or rowd.get('student') or rowd.get('reg') or rowd.get('regno') or rowd.get('reg_no') or rowd.get('index') or '',
                            'student_name': rowd.get('name') or rowd.get('student') or '',
                            'course_code': rowd.get('course code') or rowd.get('code') or rowd.get('course') or '',
                            'course_title': rowd.get('course title') or rowd.get('title') or '',
                            'quiz_score': rowd.get('quiz') or rowd.get('cat') or '',
                            'exam_score': rowd.get('exam') or rowd.get('score') or rowd.get('mark') or '',
                            'raw_row': row,
                        }
                        # only append if at least course and student data present
                        if entry['course_code'] or entry['course_title']:
                            results.append(entry)

    return results


def main():
    out = {'courses': [], 'results': []}
    if os.path.exists(DOCX_PATH):
        try:
            courses = extract_courses_from_docx(DOCX_PATH)
            out['courses'] = courses
            print(f"Extracted {len(courses)} courses from DOCX")
        except Exception as e:
            print("Error extracting DOCX:", e)
    else:
        print(f"DOCX not found at {DOCX_PATH}")

    results = extract_results_from_pdfs(RESULT_PDFS)
    out['results'] = results
    print(f"Extracted {len(results)} result rows from PDFs")

    out_path = Path('scripts') / 'courses_and_results_preview.json'
    out_path.write_text(json.dumps(out, indent=2, ensure_ascii=False))
    print(f"Wrote preview to {out_path}")


if __name__ == '__main__':
    main()
