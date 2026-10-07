"""Génère les icônes de l'application (Android + iOS) à partir du logo officiel media/lo/logo.png.

Usage : python3 tool/make_icons.py        (nécessite Pillow ; les fichiers générés sont déjà fournis dans tool/icons/)
"""
from pathlib import Path
from PIL import Image, ImageFilter

ROOT = Path(__file__).resolve().parent
SRC = ROOT.parent.parent / "media" / "lo" / "logo.png"
OUT = ROOT / "icons"
NAVY = (27, 42, 68, 255)

logo = Image.open(SRC).convert("RGBA")
sun = logo.crop((100, 12, 362, 272))          # soleil
from PIL import ImageDraw
_m = Image.new("L", sun.size, 0)               # masque circulaire : écarte les lettres voisines (« Chrétiens »)
ImageDraw.Draw(_m).ellipse((4, 4, sun.width - 6, sun.height - 6), fill=255)
sun.putalpha(Image.composite(sun.split()[3], Image.new("L", sun.size, 0), _m))
letters = logo.crop((388, 26, 664, 182))      # lettres « RCR » (blanches)


def mark(size: int, scale: float) -> Image.Image:
    """Symbole (soleil + RCR) centré sur un calque transparent carré, `scale` = part de la taille occupée."""
    layer = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    d = int(size * scale)
    s = sun.resize((d, d), Image.LANCZOS)
    sx, sy = (size - d) // 2, (size - d) // 2 - int(size * 0.02)
    layer.alpha_composite(s, (sx, sy))
    lw = int(d * 0.86)
    lh = int(letters.height * lw / letters.width)
    lt = letters.resize((lw, lh), Image.LANCZOS)
    # ombre douce pour détacher les lettres blanches du centre clair du soleil
    shadow = Image.new("RGBA", lt.size, (0, 0, 0, 0))
    shadow.putalpha(lt.split()[3].point(lambda a: int(a * 0.55)))
    shadow = Image.merge("RGBA", (Image.new("L", lt.size, 20), Image.new("L", lt.size, 30), Image.new("L", lt.size, 50), shadow.split()[3]))
    shadow = shadow.filter(ImageFilter.GaussianBlur(max(1, size // 120)))
    lx, ly = sx + (d - lw) // 2 + int(d * 0.03), sy + int(d * 0.30)
    layer.alpha_composite(shadow, (lx + max(1, size // 160), ly + max(1, size // 160)))
    layer.alpha_composite(lt, (lx, ly))
    return layer


def on_navy(size: int, scale: float, shape: str = "square") -> Image.Image:
    img = Image.new("RGBA", (size, size), NAVY)
    img.alpha_composite(mark(size, scale))
    if shape == "round":
        m = Image.new("L", (size * 4, size * 4), 0)
        from PIL import ImageDraw
        ImageDraw.Draw(m).ellipse((0, 0, size * 4 - 1, size * 4 - 1), fill=255)
        m = m.resize((size, size), Image.LANCZOS)
        img.putalpha(m)
    return img


def save(img: Image.Image, path: Path, flat: bool = False):
    path.parent.mkdir(parents=True, exist_ok=True)
    if flat:  # iOS : pas de transparence
        bg = Image.new("RGB", img.size, NAVY[:3]); bg.paste(img, mask=img.split()[3]); bg.save(path, optimize=True)
    else:
        img.save(path, optimize=True)


# --- Android : icônes classiques + adaptatives (fond uni + symbole dans la zone de sécurité 66/108)
for dens, px in {"mdpi": 48, "hdpi": 72, "xhdpi": 96, "xxhdpi": 144, "xxxhdpi": 192}.items():
    save(on_navy(px, 0.80), OUT / "android" / f"mipmap-{dens}" / "ic_launcher.png")
    save(on_navy(px, 0.80, "round"), OUT / "android" / f"mipmap-{dens}" / "ic_launcher_round.png")
for dens, px in {"mdpi": 108, "hdpi": 162, "xhdpi": 216, "xxhdpi": 324, "xxxhdpi": 432}.items():
    save(mark(px, 0.58), OUT / "android" / f"mipmap-{dens}" / "ic_launcher_foreground.png")

# --- Play Store / site (512) et iOS (1024)
save(on_navy(512, 0.80), OUT / "icon_512.png")
ios = {  # nom : taille en pixels (modèle AppIcon de Flutter)
    "Icon-App-20x20@1x": 20, "Icon-App-20x20@2x": 40, "Icon-App-20x20@3x": 60,
    "Icon-App-29x29@1x": 29, "Icon-App-29x29@2x": 58, "Icon-App-29x29@3x": 87,
    "Icon-App-40x40@1x": 40, "Icon-App-40x40@2x": 80, "Icon-App-40x40@3x": 120,
    "Icon-App-60x60@2x": 120, "Icon-App-60x60@3x": 180,
    "Icon-App-76x76@1x": 76, "Icon-App-76x76@2x": 152, "Icon-App-83.5x83.5@2x": 167,
    "Icon-App-1024x1024@1x": 1024,
}
for name, px in ios.items():
    save(on_navy(px, 0.82), OUT / "ios" / f"{name}.png", flat=True)
print("Icônes générées dans", OUT)
