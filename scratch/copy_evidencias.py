import os
import shutil

src_dir = r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura\12_evidencias"
dst_dir = r"c:\xampp\htdocs\Proyecto_Ecopoints\public\img\evidencias"

os.makedirs(dst_dir, exist_ok=True)
files = [f for f in os.listdir(src_dir) if f.endswith('.png')]
for f in files:
    src_file = os.path.join(src_dir, f)
    dst_file = os.path.join(dst_dir, f)
    shutil.copy2(src_file, dst_file)

print(f"Copied {len(files)} evidence charts to {dst_dir} successfully!")
