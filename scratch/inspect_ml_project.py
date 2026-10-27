import os
import sys

# Ensure UTF-8 output
sys.stdout.reconfigure(encoding='utf-8')

base_dir = r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura"

print("=== REVISIÓN Y ANÁLISIS DE LA CARPETA ECOPOINTS_ESTRUCTURA ===")
folders = [f for f in os.listdir(base_dir) if os.path.isdir(os.path.join(base_dir, f)) and not f.startswith('.')]
folders.sort()

for folder in folders:
    fpath = os.path.join(base_dir, folder)
    items = os.listdir(fpath)
    print(f"\n[CARPETA: {folder}] ({len(items)} elementos):")
    for item in items[:25]:
        full_p = os.path.join(fpath, item)
        if os.path.isfile(full_p):
            size_kb = os.path.getsize(full_p) / 1024
            print(f"   - [FILE] {item} ({size_kb:.1f} KB)")
        else:
            try:
                subitems = len(os.listdir(full_p))
                print(f"   - [DIR]  {item}/ ({subitems} subelementos)")
            except Exception:
                print(f"   - [DIR]  {item}/")
    if len(items) > 25:
        print(f"   ... y {len(items)-25} elementos más.")

print("\nArchivos raíz:")
for item in os.listdir(base_dir):
    full_p = os.path.join(base_dir, item)
    if os.path.isfile(full_p):
        print(f"   - {item} ({os.path.getsize(full_p)/1024:.1f} KB)")
