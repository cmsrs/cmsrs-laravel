#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Generate 76 demo products for demo.cmsrs.pl.

Output:
products/
    books/
        it_books/
        education/
    digital_products/
        e_books/
        pdf_materials/

Each product:
    - SVG cover (400x400)
    - JPG cover (400x400)
    - one primary tag
    - additional related tags

Also generates products.csv.
"""

from pathlib import Path
import argparse
import csv
import html
from io import BytesIO

import cairosvg
from PIL import Image


# ============================================================
# TAGS
# ============================================================

TAGS = [
    (1, "Primary School", "Education Level"),
    (2, "High School", "Education Level"),
    (3, "University", "Education Level"),
    (4, "Self-study", "Education Level"),

    (5, "Programming", "Subject"),
    (6, "Databases", "Subject"),
    (7, "Graphic Design", "Subject"),
    (8, "Project Management", "Subject"),
    (9, "English Learning", "Subject"),

    (10, "PHP", "Programming Language"),
    (11, "Java", "Programming Language"),
    (12, "C++", "Programming Language"),
    (13, "Python", "Programming Language"),
    (14, "JavaScript", "Programming Language"),

    (15, "A1 - Beginner", "Language Level"),
    (16, "A2 - Elementary", "Language Level"),
    (17, "B1 - Intermediate", "Language Level"),
    (18, "B2 - Upper Intermediate", "Language Level"),
    (19, "C1 - Advanced", "Language Level"),
]


# ============================================================
# CATEGORIES
# ============================================================

CATEGORIES = {
    "it_books": ("books", "it_books"),
    "education": ("books", "education"),
    "e_books": ("digital_products", "e_books"),
    "pdf_materials": ("digital_products", "pdf_materials"),
}


# ============================================================
# COLORS
# ============================================================

PALETTES = [
    ("#14213D", "#FCA311", "#FFFFFF"),
    ("#153B50", "#35C6B0", "#F4F7F8"),
    ("#392F5A", "#E0AFA0", "#FFFFFF"),
    ("#283618", "#DDA15E", "#FEFAE0"),
    ("#3D405B", "#F2CC8F", "#FFFFFF"),
    ("#5B2333", "#F7B267", "#FFF8F0"),
    ("#174A5B", "#A8DADC", "#FFFFFF"),
    ("#343A40", "#9BDEAC", "#FFFFFF"),
]


# ============================================================
# PRODUCT TITLES
# ============================================================

TITLES = {

    "Primary School": [
        "Math Adventures",
        "Learning ABC",
        "Science for Kids",
        "Creative Workbook",
    ],

    "High School": [
        "Study Smart",
        "Exam Preparation",
        "Science Essentials",
        "Mathematics Workbook",
    ],

    "University": [
        "Academic Toolkit",
        "Research Methods",
        "Study Strategies",
        "Lecture Companion",
    ],

    "Self-study": [
        "Learn at Your Pace",
        "Independent Learning",
        "Practical Study Guide",
        "Skills Workbook",
    ],

    "Programming": [
        "Programming Foundations",
        "Code by Example",
        "Developer Handbook",
        "Practical Coding",
    ],

    "Databases": [
        "Database Design",
        "SQL in Practice",
        "Data Modeling",
        "Query Workshop",
    ],

    "Graphic Design": [
        "Visual Design Basics",
        "Creative Layouts",
        "Typography Handbook",
        "Design Principles",
    ],

    "Project Management": [
        "Project Planning",
        "Agile Toolkit",
        "Team Management",
        "Project Handbook",
    ],

    "English Learning": [
        "Everyday English",
        "English Practice",
        "Build Your Vocabulary",
        "English Skills",
    ],

    "PHP": [
        "PHP Essentials",
        "Modern PHP",
        "PHP Projects",
        "Web Development with PHP",
    ],

    "Java": [
        "Java Essentials",
        "Object-Oriented Java",
        "Java Projects",
        "Modern Java",
    ],

    "C++": [
        "C++ Fundamentals",
        "Modern C++",
        "C++ Practice",
        "Programming with C++",
    ],

    "Python": [
        "Python Essentials",
        "Practical Python",
        "Python Projects",
        "Data with Python",
    ],

    "JavaScript": [
        "JavaScript Essentials",
        "Modern JavaScript",
        "JavaScript Projects",
        "Web Scripts",
    ],

    "A1 - Beginner": [
        "English A1 Starter",
        "First English Steps",
        "A1 Vocabulary",
        "Beginner Grammar",
    ],

    "A2 - Elementary": [
        "English A2 Practice",
        "Elementary Vocabulary",
        "A2 Grammar",
        "Everyday Phrases",
    ],

    "B1 - Intermediate": [
        "English B1 Skills",
        "Intermediate Grammar",
        "B1 Vocabulary",
        "Confident English",
    ],

    "B2 - Upper Intermediate": [
        "English B2 Mastery",
        "Advanced Conversations",
        "B2 Grammar",
        "Fluent English",
    ],

    "C1 - Advanced": [
        "English C1 Mastery",
        "Advanced English Skills",
        "C1 Vocabulary",
        "Academic English",
    ],
}


# ============================================================
# RELATED TAGS
# ============================================================

RELATED_TAGS = {
    1: [9, 15],
    2: [9, 16],
    3: [9, 17],
    4: [9, 15],

    5: [13, 14],
    6: [13, 3],
    7: [3, 4],
    8: [3, 4],
    9: [15, 16],

    10: [5, 3],
    11: [5, 3],
    12: [5, 3],
    13: [5, 3],
    14: [5, 3],

    15: [9, 1],
    16: [9, 2],
    17: [9, 3],
    18: [9, 3],
    19: [9, 3],
}


# ============================================================
# CATEGORY ASSIGNMENT
# ============================================================

def get_category(tag_id, variation):

    # Education
    if tag_id in (1, 2, 3, 4):

        if variation <= 2:
            return "education"

        return "e_books"

    # IT subjects
    if tag_id in (5, 6, 7, 8):

        if variation <= 2:
            return "it_books"

        return "e_books"

    # English learning
    if tag_id == 9:
        return "pdf_materials"

    # Programming languages
    if tag_id in (10, 11, 12, 13, 14):

        if variation <= 2:
            return "it_books"

        return "e_books"

    # English language levels
    if tag_id in (15, 16, 17, 18, 19):
        return "pdf_materials"

    raise ValueError(f"Unknown tag ID: {tag_id}")


# ============================================================
# SVG GENERATOR
# ============================================================

def escape(value):
    return html.escape(str(value), quote=True)


def make_svg(title, category, tag_names, number, palette):

    dark, accent, light = palette

    title_lines = []
    words = title.split()
    line = ""

    for word in words:

        if len(line) + len(word) + 1 > 19:

            title_lines.append(line)
            line = word

        else:
            line = (line + " " + word).strip()

    if line:
        title_lines.append(line)

    title_lines = title_lines[:3]

    title_svg = "".join(
        f'<text x="34" y="{205 + i * 39}" class="title">'
        f'{escape(text)}</text>'
        for i, text in enumerate(title_lines)
    )

    tags_text = " • ".join(tag_names[:2])

    return f'''<svg xmlns="http://www.w3.org/2000/svg"
        width="400" height="400" viewBox="0 0 400 400">

    <defs>

        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{dark}"/>
            <stop offset="100%" stop-color="#101820"/>
        </linearGradient>

        <pattern id="dots" width="22" height="22"
            patternUnits="userSpaceOnUse">

            <circle cx="3" cy="3" r="1.5"
                fill="{light}" opacity=".16"/>

        </pattern>

    </defs>

    <rect width="400" height="400" fill="url(#bg)"/>
    <rect width="400" height="400" fill="url(#dots)"/>

    <circle cx="350" cy="70" r="92"
        fill="{accent}" opacity=".18"/>

    <path d="M0 315 Q145 260 260 330 T440 300
        L440 420 L0 420Z"
        fill="{accent}" opacity=".16"/>

    <rect x="28" y="28" width="344" height="344"
        rx="16" fill="none"
        stroke="{light}" stroke-opacity=".25"/>

    <rect x="34" y="42" width="9" height="48"
        rx="4" fill="{accent}"/>

    <text x="58" y="61" class="eyebrow">
        {escape(category.upper())}
    </text>

    <text x="58" y="82" class="small">
        DEMO COLLECTION / {number:03d}
    </text>

    <g>

        <path d="M285 112 l28 -18 28 18 v42
            l-28 18 -28 -18z"
            fill="{accent}"/>

        <path d="M313 94 v78 M285 112 l28 18 28 -18"
            stroke="{dark}" stroke-width="3"
            fill="none"/>

    </g>

    {title_svg}

    <rect x="34" y="292" width="332" height="1"
        fill="{light}" opacity=".35"/>

    <text x="34" y="321" class="tag">
        {escape(tags_text[:42])}
    </text>

    <text x="34" y="350" class="footer">
        LEARN • CREATE • GROW
    </text>

    <text x="366" y="350" text-anchor="end" class="footer">
        #{number:03d}
    </text>

    <style>

        text {{
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }}

        .eyebrow {{
            fill: {accent};
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.7px;
        }}

        .small {{
            fill: {light};
            opacity: .62;
            font-size: 9px;
            letter-spacing: 1.2px;
        }}

        .title {{
            fill: {light};
            font-size: 27px;
            font-weight: 700;
        }}

        .tag {{
            fill: {light};
            opacity: .82;
            font-size: 11px;
        }}

        .footer {{
            fill: {light};
            opacity: .58;
            font-size: 9px;
            letter-spacing: 1.2px;
        }}

    </style>

</svg>'''


# ============================================================
# MAIN
# ============================================================

def main():

    parser = argparse.ArgumentParser()

    # Default "." means current directory.
    parser.add_argument(
        "--output",
        default=".",
        help="Output directory (default: current directory)"
    )

    args = parser.parse_args()

    output_root = Path(args.output)

    output_root.mkdir(parents=True, exist_ok=True)

    tag_by_id = {
        tag_id: name
        for tag_id, name, group in TAGS
    }

    rows = []

    product_no = 0

    for tag_id, tag_name, tag_group in TAGS:

        for variation in range(1, 5):

            product_no += 1

            # Primary and secondary tags
            assigned_ids = [
                tag_id
            ] + RELATED_TAGS.get(tag_id, [])

            assigned_names = [
                tag_by_id[i]
                for i in assigned_ids
            ]

            # Correct category based on product subject
            category_key = get_category(tag_id, variation)

            top_category, leaf_category = CATEGORIES[category_key]

            folder = (
                output_root
                / top_category
                / leaf_category
            )

            folder.mkdir(parents=True, exist_ok=True)

            # Product filename
            tag_suffix = "_".join(
                f"{i:02d}"
                for i in assigned_ids
            )

            filename = (
                f"photo_{product_no:03d}_{tag_suffix}"
            )

            title = TITLES[tag_name][variation - 1]

            category_label = (
                f"{top_category} / {leaf_category}"
            )

            palette = PALETTES[
                (product_no - 1) % len(PALETTES)
            ]

            # Generate SVG
            svg = make_svg(
                title,
                category_label,
                assigned_names,
                product_no,
                palette
            )

            svg_path = folder / f"{filename}.svg"
            jpg_path = folder / f"{filename}.jpg"

            svg_path.write_text(
                svg,
                encoding="utf-8"
            )

            # Convert SVG -> JPG
            png_bytes = cairosvg.svg2png(
                bytestring=svg.encode("utf-8"),
                output_width=400,
                output_height=400
            )

            with Image.open(BytesIO(png_bytes)) as image:

                image.convert("RGB").save(
                    jpg_path,
                    "JPEG",
                    quality=92,
                    optimize=True
                )

            # CSV metadata
            rows.append({

                "product_no": product_no,

                "title": title,

                "category": category_label,

                "primary_tag_id": tag_id,

                "primary_tag": tag_name,

                "all_tag_ids": ";".join(
                    map(str, assigned_ids)
                ),

                "all_tags": ";".join(
                    assigned_names
                ),

                "svg": str(svg_path),

                "jpg": str(jpg_path),

            })

    # Save CSV
    csv_path = output_root / "products.csv"

    with csv_path.open(
        "w",
        newline="",
        encoding="utf-8-sig"
    ) as file:

        writer = csv.DictWriter(
            file,
            fieldnames=rows[0].keys()
        )

        writer.writeheader()
        writer.writerows(rows)

    # Summary
    print()
    print("=" * 50)
    print("PRODUCT GENERATOR FINISHED")
    print("=" * 50)

    print(f"Products generated: {product_no}")
    print(f"SVG files:          {product_no}")
    print(f"JPG files:          {product_no}")

    print()
    print("CATEGORY SUMMARY:")

    for category_key, (top, leaf) in CATEGORIES.items():

        count = sum(
            1 for row in rows
            if row["category"] == f"{top} / {leaf}"
        )

        print(f"{top}/{leaf}: {count} products")

    print()
    print(f"Output directory: {output_root.resolve()}")
    print(f"CSV file:         {csv_path.resolve()}")

    print("=" * 50)


if __name__ == "__main__":
    main()
