import os
from PIL import Image

def generate_icons():
    # Source master image generated for Rudra PG
    source_path = r'C:\Users\Admin\.gemini\antigravity-ide\brain\6f6ac82e-1242-4c23-aeb9-afc4f054bc8e\rudra_icon_fullbleed_1788867707094.jpg'
    
    if not os.path.exists(source_path):
        raise FileNotFoundError(f"Source icon not found at {source_path}")
        
    master = Image.open(source_path).convert('RGBA')
    print(f"Loaded master icon: {master.size}")
    
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    
    # 1. Assets directory for in-app branding
    assets_icons_dir = os.path.join(base_dir, 'assets', 'icons')
    os.makedirs(assets_icons_dir, exist_ok=True)
    master.save(os.path.join(assets_icons_dir, 'app_icon.png'), 'PNG')
    master.resize((512, 512), Image.Resampling.LANCZOS).save(os.path.join(assets_icons_dir, 'logo.png'), 'PNG')
    print(f"Saved master in-app assets to {assets_icons_dir}")
    
    # 2. Android Mipmaps
    android_res_dir = os.path.join(base_dir, 'android', 'app', 'src', 'main', 'res')
    android_sizes = {
        'mipmap-mdpi': 48,
        'mipmap-hdpi': 72,
        'mipmap-xhdpi': 96,
        'mipmap-xxhdpi': 144,
        'mipmap-xxxhdpi': 192,
    }
    
    for folder, size in android_sizes.items():
        target_dir = os.path.join(android_res_dir, folder)
        os.makedirs(target_dir, exist_ok=True)
        resized = master.resize((size, size), Image.Resampling.LANCZOS)
        resized.save(os.path.join(target_dir, 'ic_launcher.png'), 'PNG')
        print(f"Saved Android {folder}/ic_launcher.png ({size}x{size})")
        
    # 3. iOS AppIcon set
    ios_icon_dir = os.path.join(base_dir, 'ios', 'Runner', 'Assets.xcassets', 'AppIcon.appiconset')
    os.makedirs(ios_icon_dir, exist_ok=True)
    
    ios_sizes = {
        'Icon-App-1024x1024@1x.png': 1024,
        'Icon-App-83.5x83.5@2x.png': 167,
        'Icon-App-76x76@1x.png': 76,
        'Icon-App-76x76@2x.png': 152,
        'Icon-App-60x60@2x.png': 120,
        'Icon-App-60x60@3x.png': 180,
        'Icon-App-40x40@1x.png': 40,
        'Icon-App-40x40@2x.png': 80,
        'Icon-App-40x40@3x.png': 120,
        'Icon-App-29x29@1x.png': 29,
        'Icon-App-29x29@2x.png': 58,
        'Icon-App-29x29@3x.png': 87,
        'Icon-App-20x20@1x.png': 20,
        'Icon-App-20x20@2x.png': 40,
        'Icon-App-20x20@3x.png': 60,
    }
    
    for filename, size in ios_sizes.items():
        resized = master.resize((size, size), Image.Resampling.LANCZOS)
        # iOS App Store guidelines require icons to not have alpha transparency
        rgb_icon = Image.new('RGB', (size, size), (11, 28, 68))
        rgb_icon.paste(resized, mask=resized.split()[3])
        rgb_icon.save(os.path.join(ios_icon_dir, filename), 'PNG')
        print(f"Saved iOS {filename} ({size}x{size})")
        
    # 4. Web icons
    web_dir = os.path.join(base_dir, 'web')
    web_icons_dir = os.path.join(web_dir, 'icons')
    os.makedirs(web_icons_dir, exist_ok=True)
    
    master.resize((32, 32), Image.Resampling.LANCZOS).save(os.path.join(web_dir, 'favicon.png'), 'PNG')
    master.resize((192, 192), Image.Resampling.LANCZOS).save(os.path.join(web_icons_dir, 'Icon-192.png'), 'PNG')
    master.resize((512, 512), Image.Resampling.LANCZOS).save(os.path.join(web_icons_dir, 'Icon-512.png'), 'PNG')
    master.resize((192, 192), Image.Resampling.LANCZOS).save(os.path.join(web_icons_dir, 'Icon-maskable-192.png'), 'PNG')
    master.resize((512, 512), Image.Resampling.LANCZOS).save(os.path.join(web_icons_dir, 'Icon-maskable-512.png'), 'PNG')
    print("Saved Web icons and favicon.")

if __name__ == '__main__':
    generate_icons()
