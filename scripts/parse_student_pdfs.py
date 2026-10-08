import json
import re
import pdfplumber
from pathlib import Path

BASE = Path(r"C:/Users/sammy/Desktop/All students")
INPUTS = {
    'ho': BASE / 'STUDENT INFO (HO BRANCH).pdf',
    'kasoa': BASE / 'pre college students info.pdf',
}

FIELD_MAP = {
    'ho': {
        'name': 1,
        'department': 2,
        'dob': 8,
        'gender': 9,
        'marital': 10,
        'qual': 11,
        'native_town': 12,
        'contact': 16,
        'nationality': 17,
        'phone': 18,
        'enrol': 19,
        'email': 20,
        'mode': 25,
        'profession': 26,
    },
    'kasoa': {
        'name': 1,
        'department': 2,
        'dob': 6,
        'gender': 7,
        'marital': 8,
        'qual': 9,
        'native_town': 10,
        'contact': 13,
        'nationality': 14,
        'phone': 15,
        'enrol': 18,
        'email': 21,
        'mode': 23,
        'profession': 27,
    },
}

EMAIL_RE = re.compile(r'([A-Za-z0-9._%+-]+(?:\s+[A-Za-z0-9._%+-]+)*)\s*[@]\s*([A-Za-z0-9.-]+(?:\s+[A-Za-z0-9.-]+)*)\s*[.]\s*([A-Za-z]{2,})')
PHONE_RE = re.compile(r'\+?[0-9][0-9\s\-]{6,}[0-9]')


def normalize_cell(cell):
    if cell is None:
        return ''
    return ' '.join(cell.split()).strip()


def merge_rows(rows):
    merged = []
    current = None
    for row in rows:
        if row and row[0] and row[0].strip().isdigit():
            if current is not None:
                merged.append(current)
            current = [normalize_cell(c) for c in row]
        elif current is not None:
            for idx, cell in enumerate(row):
                text = normalize_cell(cell)
                if not text:
                    continue
                if current[idx]:
                    current[idx] = current[idx] + ' ' + text
                else:
                    current[idx] = text
        else:
            continue
    if current is not None:
        merged.append(current)
    return merged


def normalize_email_text(text: str) -> str:
    if not text:
        return ''
    text = text.replace(' @ ', '@').replace(' @', '@').replace('@ ', '@')
    text = text.replace(' . ', '.').replace(' .', '.').replace('. ', '.')
    return text


def cleanup_email_component(text: str) -> str:
    return re.sub(r'\s+', '', text)


def guess_email(row, field_map):
    candidates = []
    if field_map['email'] < len(row):
        candidates.append(normalize_email_text(normalize_cell(row[field_map['email']])))
    for val in row:
        if val and '@' in str(val):
            candidates.append(normalize_email_text(normalize_cell(val)))
    for cand in candidates:
        m = EMAIL_RE.search(cand)
        if m:
            local, domain, tld = m.groups()
            return cleanup_email_component(local) + '@' + cleanup_email_component(domain) + '.' + tld
    joined = ' '.join(normalize_cell(val) for val in row)
    joined = normalize_email_text(joined)
    m = EMAIL_RE.search(joined)
    if m:
        local, domain, tld = m.groups()
        return cleanup_email_component(local) + '@' + cleanup_email_component(domain) + '.' + tld
    return ''


def guess_phone(row, field_map):
    phone = normalize_cell(row[field_map['phone']]) if field_map['phone'] < len(row) else ''
    m = PHONE_RE.search(phone)
    if m:
        return re.sub(r'[\s\-]', '', m.group(0))
    joined = ' '.join(normalize_cell(val) for val in row)
    m = PHONE_RE.search(joined)
    return re.sub(r'[\s\-]', '', m.group(0)) if m else ''


def clean_program(department: str, key: str) -> str:
    val = normalize_cell(department).lower()
    if 'pre' in val:
        return 'pre_college'
    if 'first' in val:
        return 'first_semester'
    if 'second' in val:
        return 'second_semester'
    if 'third' in val and 'practical' in val:
        return 'third_semester_practical'
    if 'third' in val:
        return 'third_semester'
    return 'pre_college'


def find_mode(row):
    for val in row:
        text = normalize_cell(val).lower()
        if text in {'full time', 'full', 'part time', 'part'}:
            return 'Full Time' if 'full' in text else 'Part Time'
    return ''


def find_profession(row, current_mode):
    skip_nationality = {'ghanaian', 'liberian', 'nigerian', 'kenyan', 'american', 'british'}
    for val in reversed(row):
        text = normalize_cell(val)
        if not text:
            continue
        low = text.lower()
        if low in {'full time', 'full', 'part time', 'part'}:
            continue
        if EMAIL_RE.search(text):
            continue
        if PHONE_RE.search(text):
            continue
        if re.fullmatch(r'\d{4}', text):
            continue
        if '(' in text or ')' in text:
            continue
        if low in skip_nationality:
            continue
        return text
    return ''


def parse_row(row, key):
    fields = FIELD_MAP[key]
    department = normalize_cell(row[fields['department']]) if fields['department'] < len(row) else ''
    mode = normalize_cell(row[fields['mode']]) if fields['mode'] < len(row) else ''
    if not mode:
        mode = find_mode(row)
    profession = normalize_cell(row[fields['profession']]) if fields['profession'] < len(row) else ''
    if not profession:
        profession = find_profession(row, mode)
    obj = {
        'source': key,
        'name': normalize_cell(row[fields['name']]) if fields['name'] < len(row) else '',
        'email': guess_email(row, fields),
        'dob': normalize_cell(row[fields['dob']]) if fields['dob'] < len(row) else '',
        'gender': normalize_cell(row[fields['gender']]) if fields['gender'] < len(row) else '',
        'marital': normalize_cell(row[fields['marital']]) if fields['marital'] < len(row) else '',
        'qual': normalize_cell(row[fields['qual']]) if fields['qual'] < len(row) else '',
        'native_town': normalize_cell(row[fields['native_town']]) if fields['native_town'] < len(row) else '',
        'nationality': normalize_cell(row[fields['nationality']]) if fields['nationality'] < len(row) else '',
        'phone': guess_phone(row, fields),
        'enrol': normalize_cell(row[fields['enrol']]) if fields['enrol'] < len(row) else '',
        'mode': mode,
        'profession': profession,
        'department': department,
        'branch': 'HO' if key == 'ho' else 'KA',
        'program': clean_program(department, key),
    }
    return obj


def process_pdf(key, path):
    students = []
    with pdfplumber.open(path) as pdf:
        for page in pdf.pages:
            table = page.extract_table()
            if not table:
                continue
            rows = merge_rows(table[1:])
            for row in rows:
                if len(row) <= 1:
                    continue
                if not normalize_cell(row[1]):
                    continue
                students.append(parse_row(row, key))
    return students


def main():
    out = {}
    for key, path in INPUTS.items():
        students = process_pdf(key, path)
        out[key] = students
        print(f'{key}: {len(students)} parsed students')
        for s in students[:5]:
            print(s)
        print()
    with open('scripts/student_import_preview.json', 'w', encoding='utf-8') as f:
        json.dump(out, f, indent=2, ensure_ascii=False)
    print('Wrote scripts/student_import_preview.json')


if __name__ == '__main__':
    main()
