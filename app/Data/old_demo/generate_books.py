import os
import io
import random
import cairosvg
import html
from PIL import Image

# ==========================
# KONFIGURACJA
# ==========================

OUTPUT_DIR = "books"
SIZE = 400

os.makedirs(OUTPUT_DIR, exist_ok=True)

books = [
    # ANGIELSKI - 6 sztuk
    {
        "filename": "english_01.jpg",
        "title": "ENGLISH",
        "subtitle": "Grammar Essentials",
        "description": "Master the Basics",
        "category": "LANGUAGE LEARNING",
        "number": "01",
        "colors": ["#14213D", "#FCA311", "#FFFFFF"],
        "style": 1
    },
    {
        "filename": "english_02.jpg",
        "title": "VOCABULARY",
        "subtitle": "Build Your Words",
        "description": "Learn 1000 Essential Words",
        "category": "ENGLISH COURSE",
        "number": "02",
        "colors": ["#006D77", "#83C5BE", "#EDF6F9"],
        "style": 2
    },
    {
        "filename": "english_03.jpg",
        "title": "SPEAKING",
        "subtitle": "Speak with Confidence",
        "description": "Everyday English Practice",
        "category": "PRACTICAL ENGLISH",
        "number": "03",
        "colors": ["#6A040F", "#DC2F02", "#FFF3E0"],
        "style": 3
    },
    {
        "filename": "english_04.jpg",
        "title": "LISTENING",
        "subtitle": "Train Your Ear",
        "description": "Understand Native Speakers",
        "category": "ENGLISH TRAINING",
        "number": "04",
        "colors": ["#240046", "#5A189A", "#E0AAFF"],
        "style": 4
    },
    {
        "filename": "english_05.jpg",
        "title": "ENGLISH",
        "subtitle": "Advanced Reading",
        "description": "Read. Understand. Improve.",
        "category": "LEVEL B2 / C1",
        "number": "05",
        "colors": ["#283618", "#606C38", "#FEFAE0"],
        "style": 5
    },
    {
        "filename": "english_06.jpg",
        "title": "DAILY ENGLISH",
        "subtitle": "30 Days of Learning",
        "description": "A New Lesson Every Day",
        "category": "STUDY WORKBOOK",
        "number": "06",
        "colors": ["#003049", "#669BBC", "#FDF0D5"],
        "style": 6
    },

    # JAVA - 1 sztuka
    {
        "filename": "java_01.jpg",
        "title": "JAVA",
        "subtitle": "Programming Masterclass",
        "description": "From Zero to Developer",
        "category": "SOFTWARE DEVELOPMENT",
        "number": "07",
        "colors": ["#370617", "#E85D04", "#FFF1E6"],
        "style": 3
    },

    # PHP - 2 sztuki
    {
        "filename": "php_01.jpg",
        "title": "PHP",
        "subtitle": "Modern Web Development",
        "description": "Build Dynamic Websites",
        "category": "BACKEND DEVELOPMENT",
        "number": "08",
        "colors": ["#03045E", "#4361EE", "#CAF0F8"],
        "style": 4
    },
    {
        "filename": "php_02.jpg",
        "title": "PHP and MYSQL",
        "subtitle": "Web Application Design",
        "description": "Database Driven Projects",
        "category": "WEB PROGRAMMING",
        "number": "09",
        "colors": ["#1B4332", "#40916C", "#D8F3DC"],
        "style": 2
    },

    # PROJECT MANAGEMENT - 1 sztuka
    {
        "filename": "project_management_01.jpg",
        "title": "PROJECT",
        "subtitle": "Management",
        "description": "Plan. Execute. Deliver.",
        "category": "PROFESSIONAL DEVELOPMENT",
        "number": "10",
        "colors": ["#1D3557", "#457B9D", "#F1FAEE"],
        "style": 5
    },

    {
        "filename": "sport_socks_01.jpg",
        "title": "SPORT SOCKS",
        "subtitle": "Performance & Comfort",
        "description": "Designed for Active Lifestyle",
        "category": "SPORTS ACCESSORIES",
        "number": "11",
        "colors": ["#14213D", "#00B4D8", "#FFFFFF"],
        "style": 7
    }
]


# ==========================
# GENEROWANIE DEKORACJI SVG
# ==========================

def decorations(style, color):
    if style == 1:
        return f"""
        <rect x="250" y="0" width="150" height="400"
              fill="{color}" opacity="0.12"/>
        <circle cx="320" cy="95" r="60"
                fill="{color}" opacity="0.25"/>
        <circle cx="320" cy="95" r="38"
                fill="none" stroke="{color}" stroke-width="3"/>
        <path d="M250 250 L400 100" stroke="{color}"
              stroke-width="2" opacity="0.3"/>
        <path d="M250 290 L400 140" stroke="{color}"
              stroke-width="2" opacity="0.3"/>
        """

    elif style == 2:
        return f"""
        <circle cx="350" cy="40" r="120"
                fill="{color}" opacity="0.16"/>
        <circle cx="40" cy="380" r="100"
                fill="{color}" opacity="0.18"/>
        <rect x="280" y="240" width="80" height="80"
              rx="20" fill="{color}" opacity="0.3"
              transform="rotate(25 320 280)"/>
        """

    elif style == 3:
        return f"""
        <path d="M0 280 Q200 180 400 280 L400 400 L0 400Z"
              fill="{color}" opacity="0.18"/>
        <circle cx="330" cy="100" r="80"
                fill="{color}" opacity="0.22"/>
        <circle cx="330" cy="100" r="50"
                fill="none" stroke="{color}" stroke-width="4"/>
        <circle cx="330" cy="100" r="20"
                fill="{color}" opacity="0.5"/>
        """

    elif style == 4:
        return f"""
        <g opacity="0.2" stroke="{color}" fill="none">
            <circle cx="300" cy="200" r="130" stroke-width="2"/>
            <circle cx="300" cy="200" r="100" stroke-width="2"/>
            <circle cx="300" cy="200" r="70" stroke-width="2"/>
            <circle cx="300" cy="200" r="40" stroke-width="2"/>
            <path d="M170 200 H430" stroke-width="2"/>
            <path d="M300 70 V330" stroke-width="2"/>
        </g>
        """

    elif style == 5:
        return f"""
        <rect x="0" y="0" width="400" height="400"
              fill="none" stroke="{color}" stroke-width="20"
              opacity="0.3"/>
        <rect x="25" y="25" width="350" height="350"
              fill="none" stroke="{color}" stroke-width="2"
              opacity="0.4"/>
        <path d="M250 250 L380 380" stroke="{color}"
              stroke-width="30" opacity="0.15"/>
        """

    elif style == 6:
        return f"""
        <g fill="{color}" opacity="0.18">
            <rect x="260" y="50" width="20" height="20" rx="4"/>
            <rect x="290" y="50" width="20" height="20" rx="4"/>
            <rect x="320" y="50" width="20" height="20" rx="4"/>
            <rect x="350" y="50" width="20" height="20" rx="4"/>

            <rect x="260" y="80" width="20" height="20" rx="4"/>
            <rect x="290" y="80" width="20" height="20" rx="4"/>
            <rect x="320" y="80" width="20" height="20" rx="4"/>
            <rect x="350" y="80" width="20" height="20" rx="4"/>

            <rect x="260" y="110" width="20" height="20" rx="4"/>
            <rect x="290" y="110" width="20" height="20" rx="4"/>
            <rect x="320" y="110" width="20" height="20" rx="4"/>
            <rect x="350" y="110" width="20" height="20" rx="4"/>
        </g>
        """
    elif style == 7:
        return f"""
        <!-- Sport sock illustration -->
        <g transform="translate(215, 95) rotate(-15)">
            <!-- Main sock -->
            <path d="
                M80 0
                L145 0
                L145 150
                L190 150
                Q220 150 220 180
                Q220 210 185 210
                L110 210
                Q80 210 80 180
                Z"
                fill="{color}"
                opacity="0.95"/>

            <!-- Sock cuff -->
            <rect x="80" y="0" width="65" height="35"
                  rx="5" fill="{color}"/>

            <!-- Stripes -->
            <rect x="80" y="45" width="65" height="8"
                  fill="#FFFFFF" opacity="0.8"/>
            <rect x="80" y="60" width="65" height="8"
                  fill="#FFFFFF" opacity="0.8"/>

            <!-- Heel detail -->
            <path d="M80 130 Q110 155 145 145"
                  fill="none" stroke="#FFFFFF"
                  stroke-width="8" opacity="0.5"/>
        </g>

        <!-- Second sock -->
        <g transform="translate(110, 155) rotate(15)"
           opacity="0.65">
            <path d="
                M80 0
                L145 0
                L145 150
                L190 150
                Q220 150 220 180
                Q220 210 185 210
                L110 210
                Q80 210 80 180
                Z"
                fill="{color}"/>
        </g>
        """

    return ""


# ==========================
# GENEROWANIE SVG
# ==========================

def generate_svg(book):
    bg, accent, text_color = book["colors"]

    title = html.escape(book["title"])
    subtitle = html.escape(book["subtitle"])
    description = html.escape(book["description"])
    category = html.escape(book["category"])
    number = html.escape(book["number"])    

    svg = f"""
    <svg xmlns="http://www.w3.org/2000/svg"
         width="{SIZE}" height="{SIZE}"
         viewBox="0 0 {SIZE} {SIZE}">

        <!-- Background -->
        <rect width="400" height="400" fill="{bg}"/>

        <!-- Decorations -->
        {decorations(book["style"], accent)}

        <!-- Accent bar -->
        <rect x="35" y="38" width="55" height="6"
              rx="3" fill="{accent}"/>

        <!-- Category -->
        <text x="35" y="68"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="10"
              letter-spacing="2"
              fill="{text_color}"
              opacity="0.75">
            {category}
        </text>

        <!-- Main title -->
        <text x="35" y="160"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="38"
              font-weight="bold"
              fill="{text_color}">
            {title}
        </text>

        <!-- Subtitle -->
        <text x="35" y="205"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="22"
              font-weight="bold"
              fill="{accent}">
            {subtitle}
        </text>

        <!-- Description -->
        <text x="35" y="240"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="13"
              fill="{text_color}"
              opacity="0.85">
            {description}
        </text>

        <!-- Bottom accent -->
        <rect x="35" y="330" width="330" height="2"
              fill="{accent}" opacity="0.7"/>

        <!-- Footer -->
        <text x="35" y="355"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="10"
              letter-spacing="1.5"
              fill="{text_color}"
              opacity="0.7">
            LEARNING SERIES
        </text>

        <text x="365" y="355"
              text-anchor="end"
              font-family="DejaVu Sans, Arial, sans-serif"
              font-size="12"
              font-weight="bold"
              fill="{accent}">
            {number}
        </text>

    </svg>
    """

    return svg


# ==========================
# SVG -> JPG
# ==========================

def convert_svg_to_jpg(svg_content, output_path):
    # SVG -> PNG in pamięci
    png_data = cairosvg.svg2png(
        bytestring=svg_content.encode("utf-8"),
        output_width=SIZE,
        output_height=SIZE
    )

    # PNG -> JPG
    image = Image.open(io.BytesIO(png_data)).convert("RGB")

    image.save(
        output_path,
        format="JPEG",
        quality=95,
        optimize=True
    )


# ==========================
# GŁÓWNA FUNKCJA
# ==========================

def main():
    print("Generating book covers...\n")

    for index, book in enumerate(books, start=1):
        svg_content = generate_svg(book)

        # Tymczasowy plik SVG
        svg_path = os.path.join(
            OUTPUT_DIR,
            book["filename"].replace(".jpg", ".svg")
        )

        jpg_path = os.path.join(
            OUTPUT_DIR,
            book["filename"]
        )

        # Zapis SVG
        with open(svg_path, "w", encoding="utf-8") as f:
            f.write(svg_content)

        # Konwersja SVG -> JPG
        convert_svg_to_jpg(svg_content, jpg_path)

        # Usuwamy SVG po udanej konwersji
        if os.path.exists(jpg_path):
            os.remove(svg_path)

        print(f"[{index}/{len(books)}] Created: {jpg_path}")

    print("\nDone!")
    print(f"All covers saved in: {OUTPUT_DIR}")


if __name__ == "__main__":
    main()
