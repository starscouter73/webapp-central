from __future__ import annotations

import json
from dataclasses import dataclass
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
ASSET_ROOT = ROOT / "custom" / "themes" / "webapp-central-starter" / "assets" / "brand"
REJECTED_ROOT = ASSET_ROOT / "_rejected_cropped"
FONT_BOLD = Path(r"C:\Windows\Fonts\segoeuib.ttf")
FONT_REGULAR = Path(r"C:\Windows\Fonts\segoeui.ttf")
WORDMARK = "webapp-central.de"
TAGLINE = "Projekte  |  Module  |  Tutorials"


@dataclass(frozen=True)
class Variant:
    slug: str
    icon_fill: tuple[int, int, int, int]
    icon_outline: tuple[int, int, int, int]
    wordmark: tuple[int, int, int, int]
    tagline: tuple[int, int, int, int]
    border: tuple[int, int, int, int]


VARIANTS: tuple[Variant, ...] = (
    Variant("fresh-blue", (96, 195, 233, 255), (46, 135, 210, 255), (30, 60, 90, 255), (61, 116, 146, 255), (103, 201, 236, 255)),
    Variant("soft-cyan", (86, 197, 221, 255), (41, 149, 206, 255), (31, 67, 96, 255), (73, 127, 156, 255), (87, 184, 232, 255)),
    Variant("mint-glass", (88, 199, 186, 255), (50, 132, 212, 255), (32, 62, 92, 255), (68, 125, 147, 255), (92, 209, 194, 255)),
)


def ensure_dirs() -> None:
    for part in ("header", "main", "icons", "svg", "_reports"):
        (ASSET_ROOT / part).mkdir(parents=True, exist_ok=True)


def move_previous_assets() -> list[str]:
    moved: list[str] = []
    REJECTED_ROOT.mkdir(parents=True, exist_ok=True)

    for path in list((ASSET_ROOT / "header").glob("*")) + list((ASSET_ROOT / "main").glob("*")):
        target = REJECTED_ROOT / path.name
        if path.exists():
            path.replace(target)
            moved.append(str(target.relative_to(ROOT)))

    for path in (ASSET_ROOT / "svg").glob("*"):
        target = REJECTED_ROOT / path.name
        if path.exists():
            path.replace(target)
            moved.append(str(target.relative_to(ROOT)))

    for path in (ASSET_ROOT / "icons").glob("*"):
        path.unlink()
        moved.append(str(path.relative_to(ROOT)))

    return moved


def make_font(path: Path, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(str(path), size=size)


def hex_points(cx: float, cy: float, radius: float) -> list[tuple[float, float]]:
    import math

    points: list[tuple[float, float]] = []
    for idx in range(6):
        angle = math.radians(30 + idx * 60)
        points.append((cx + radius * math.cos(angle), cy + radius * math.sin(angle)))
    return points


def measure(draw: ImageDraw.ImageDraw, text: str, font: ImageFont.FreeTypeFont) -> tuple[int, int]:
    left, top, right, bottom = draw.textbbox((0, 0), text, font=font)
    return right - left, bottom - top


def fit_font(draw: ImageDraw.ImageDraw, text: str, font_path: Path, max_size: int, min_size: int, max_width: int) -> ImageFont.FreeTypeFont:
    for size in range(max_size, min_size - 1, -2):
        font = make_font(font_path, size)
        width, _ = measure(draw, text, font)
        if width <= max_width:
            return font
    return make_font(font_path, min_size)


def render_logo(
    width: int,
    height: int,
    variant: Variant,
    left_pad: int,
    right_pad: int,
    top_pad: int,
    bottom_pad: int,
    frame_margin: int,
) -> Image.Image:
    image = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    draw = ImageDraw.Draw(image)

    icon_radius = int(height * 0.21)
    icon_center_x = left_pad + icon_radius + 6
    icon_center_y = height // 2
    icon_inner = int(icon_radius * 0.82)

    draw.polygon(hex_points(icon_center_x, icon_center_y, icon_radius), fill=variant.icon_fill, outline=variant.icon_outline, width=4)
    draw.polygon(hex_points(icon_center_x, icon_center_y, icon_inner), outline=(255, 255, 255, 210), width=2)

    icon_font = fit_font(draw, "wc", FONT_BOLD, int(icon_radius * 1.05), 28, int(icon_radius * 1.35))
    icon_w, icon_h = measure(draw, "wc", icon_font)
    draw.text((icon_center_x - icon_w / 2, icon_center_y - icon_h / 2 - 4), "wc", fill=(255, 255, 255, 255), font=icon_font)

    text_start_x = icon_center_x + icon_radius + int(height * 0.11)
    usable_right = width - right_pad
    max_text_width = usable_right - text_start_x

    wordmark_font = fit_font(draw, WORDMARK, FONT_BOLD, int(height * 0.22), int(height * 0.13), max_text_width)
    tagline_font = fit_font(draw, TAGLINE, FONT_BOLD, int(height * 0.082), int(height * 0.055), max_text_width)

    wordmark_w, wordmark_h = measure(draw, WORDMARK, wordmark_font)
    tagline_w, tagline_h = measure(draw, TAGLINE, tagline_font)

    total_h = wordmark_h + tagline_h + int(height * 0.05)
    block_y = max(top_pad, (height - total_h) // 2 - 4)

    draw.text((text_start_x, block_y), WORDMARK, fill=variant.wordmark, font=wordmark_font)
    draw.text((text_start_x, block_y + wordmark_h + int(height * 0.05)), TAGLINE, fill=variant.tagline, font=tagline_font)

    return image


def render_icon(size: int, variant: Variant) -> Image.Image:
    image = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(image)
    cx = cy = size / 2
    radius = size * 0.34
    inner = radius * 0.78
    draw.polygon(hex_points(cx, cy, radius), fill=variant.icon_fill, outline=variant.icon_outline, width=max(2, size // 64))
    draw.polygon(hex_points(cx, cy, inner), outline=(255, 255, 255, 210), width=max(1, size // 180))
    font = fit_font(draw, "wc", FONT_BOLD, int(size * 0.24), 10, int(size * 0.42))
    w, h = measure(draw, "wc", font)
    draw.text((cx - w / 2, cy - h / 2 - size * 0.015), "wc", fill=(255, 255, 255, 255), font=font)
    return image


def alpha_bbox(image: Image.Image) -> tuple[int, int, int, int]:
    alpha = image.getchannel("A")
    bbox = alpha.getbbox()
    if bbox is None:
        raise RuntimeError("Image has no visible pixels.")
    return bbox


def validate_bbox(image: Image.Image, *, min_left: int, min_right: int, min_top: int = 20, min_bottom: int = 20) -> dict[str, int]:
    left, top, right, bottom = alpha_bbox(image)
    result = {
        "left": left,
        "right": image.width - right,
        "top": top,
        "bottom": image.height - bottom,
    }
    if result["left"] < min_left or result["right"] < min_right or result["top"] < min_top or result["bottom"] < min_bottom:
        raise RuntimeError(f"Bounding box check failed: {result}")
    return result


def crop_vertical_with_padding(image: Image.Image, top_pad: int, bottom_pad: int) -> Image.Image:
    left, top, right, bottom = alpha_bbox(image)
    y0 = max(0, top - top_pad)
    y1 = min(image.height, bottom + bottom_pad)
    return image.crop((0, y0, image.width, y1))


def save_svg() -> str:
    svg_path = ASSET_ROOT / "svg" / "webapp-central-header-master.svg"
    svg_path.write_text(
        """<svg xmlns="http://www.w3.org/2000/svg" width="1800" height="360" viewBox="0 0 1800 360" fill="none">
  <path d="M246 98L311.82 136V212L246 250L180.18 212V136L246 98Z" fill="#60C3E9" stroke="#2E87D2" stroke-width="4"/>
  <path d="M246 122L291.03 148V200L246 226L200.97 200V148L246 122Z" stroke="white" stroke-opacity=".85" stroke-width="2"/>
  <text x="201" y="191" fill="white" font-family="Segoe UI, Arial, sans-serif" font-size="54" font-weight="700">wc</text>
  <text x="390" y="173" fill="#1E3C5A" font-family="Segoe UI, Arial, sans-serif" font-size="96" font-weight="700">webapp-central.de</text>
  <text x="390" y="232" fill="#3D7492" font-family="Segoe UI, Arial, sans-serif" font-size="34" font-weight="700">Projekte  |  Module  |  Tutorials</text>
</svg>
""",
        encoding="utf-8",
    )
    return str(svg_path.relative_to(ROOT))


def main() -> None:
    ensure_dirs()
    moved = move_previous_assets()
    reports: dict[str, dict[str, dict[str, int] | str]] = {}

    for variant in VARIANTS:
        header = render_logo(1800, 360, variant, left_pad=120, right_pad=360, top_pad=42, bottom_pad=42, frame_margin=260)
        hero = render_logo(2400, 720, variant, left_pad=140, right_pad=520, top_pad=72, bottom_pad=72, frame_margin=380)
        icon_512 = render_icon(512, variant)
        icon_192 = render_icon(192, variant)
        icon_32 = render_icon(32, variant)

        header_box = validate_bbox(header, min_left=80, min_right=240, min_top=20, min_bottom=20)
        hero_box = validate_bbox(hero, min_left=100, min_right=360, min_top=20, min_bottom=20)
        icon_box = validate_bbox(icon_512, min_left=40, min_right=40, min_top=40, min_bottom=40)

        header_path = ASSET_ROOT / "header" / f"webapp-central-header-{variant.slug}-1800x360.png"
        hero_path = ASSET_ROOT / "main" / f"webapp-central-main-{variant.slug}-2400x720.png"
        header_display_path = ASSET_ROOT / "header" / f"webapp-central-header-{variant.slug}-display.png"
        hero_display_path = ASSET_ROOT / "main" / f"webapp-central-main-{variant.slug}-display.png"
        icon_512_path = ASSET_ROOT / "icons" / f"webapp-central-icon-{variant.slug}-512x512.png"
        icon_192_path = ASSET_ROOT / "icons" / f"webapp-central-icon-{variant.slug}-192x192.png"
        icon_32_path = ASSET_ROOT / "icons" / f"webapp-central-icon-{variant.slug}-32x32.png"

        header.save(header_path)
        hero.save(hero_path)
        crop_vertical_with_padding(header, top_pad=18, bottom_pad=18).save(header_display_path)
        crop_vertical_with_padding(hero, top_pad=24, bottom_pad=24).save(hero_display_path)
        icon_512.save(icon_512_path)
        icon_192.save(icon_192_path)
        icon_32.save(icon_32_path)

        reports[variant.slug] = {
            "header": {"file": str(header_path.relative_to(ROOT)), **header_box},
            "hero": {"file": str(hero_path.relative_to(ROOT)), **hero_box},
            "header_display": {"file": str(header_display_path.relative_to(ROOT)), **validate_bbox(crop_vertical_with_padding(header, 18, 18), min_left=80, min_right=240, min_top=18, min_bottom=18)},
            "hero_display": {"file": str(hero_display_path.relative_to(ROOT)), **validate_bbox(crop_vertical_with_padding(hero, 24, 24), min_left=100, min_right=360, min_top=18, min_bottom=18)},
            "icon": {"file": str(icon_512_path.relative_to(ROOT)), **icon_box},
        }

    report_path = ASSET_ROOT / "_reports" / "logo-generation-report.json"
    report_path.write_text(json.dumps({"moved_previous_assets": moved, "reports": reports, "svg_master": save_svg()}, indent=2), encoding="utf-8")
    print(report_path)


if __name__ == "__main__":
    main()
