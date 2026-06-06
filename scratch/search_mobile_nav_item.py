css_path = r"c:\xampp\htdocs\sparkx1\assets\dashboard\css\main-bundle.css"

with open(css_path, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    if 'mobile-nav-item' in line:
        print(f"Line {idx + 1}: {line.strip()}")
