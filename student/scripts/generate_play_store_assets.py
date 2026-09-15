import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

def create_play_store_assets():
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    output_dir = os.path.join(base_dir, 'play_store_assets')
    screenshots_dir = os.path.join(output_dir, 'screenshots')
    os.makedirs(screenshots_dir, exist_ok=True)
    
    icon_source = os.path.join(base_dir, 'assets', 'icons', 'app_icon.png')
    logo_source = os.path.join(base_dir, 'assets', 'icons', 'logo.png')
    
    # -------------------------------------------------------------
    # 1. App Icon (512x512 32-bit PNG)
    # -------------------------------------------------------------
    if os.path.exists(icon_source):
        img_icon = Image.open(icon_source).convert('RGBA')
        icon_512 = img_icon.resize((512, 512), Image.Resampling.LANCZOS)
        icon_path = os.path.join(output_dir, 'app_icon_512x512.png')
        icon_512.save(icon_path, 'PNG', optimize=True)
        print(f"Generated Play Store App Icon: {icon_path} ({os.path.getsize(icon_path)} bytes)")
        
    # -------------------------------------------------------------
    # 2. Feature Graphic (1024x500 PNG, No Alpha)
    # -------------------------------------------------------------
    fg_width, fg_height = 1024, 500
    fg = Image.new('RGB', (fg_width, fg_height), (11, 19, 43))
    draw_fg = ImageDraw.Draw(fg)
    
    # Gradient background
    for y in range(fg_height):
        ratio = y / fg_height
        r = int(11 * (1 - ratio) + 15 * ratio)
        g = int(19 * (1 - ratio) + 32 * ratio)
        b = int(43 * (1 - ratio) + 65 * ratio)
        draw_fg.line([(0, y), (fg_width, y)], fill=(r, g, b))
        
    # Subtle accent glow
    for r in range(260, 0, -10):
        alpha = int(15 * (1 - r / 260))
        draw_fg.ellipse(
            [(800 - r, 120 - r), (800 + r, 120 + r)],
            fill=(14, 116, 144)
        )
    
    # Load fonts
    font_bold_path = r'C:\Windows\Fonts\segoeuib.ttf'
    font_reg_path = r'C:\Windows\Fonts\segoeui.ttf'
    
    font_title = ImageFont.truetype(font_bold_path, 46)
    font_subtitle = ImageFont.truetype(font_bold_path, 22)
    font_badge = ImageFont.truetype(font_bold_path, 13)
    font_pill = ImageFont.truetype(font_bold_path, 15)
    
    # Draw logo icon on left
    if os.path.exists(logo_source):
        logo_img = Image.open(logo_source).convert('RGBA')
        logo_resized = logo_img.resize((150, 150), Image.Resampling.LANCZOS)
        
        # Round the corners of the logo for display
        mask = Image.new('L', (150, 150), 0)
        mask_draw = ImageDraw.Draw(mask)
        mask_draw.rounded_rectangle([(0, 0), (150, 150)], radius=34, fill=255)
        
        fg.paste(logo_resized, (90, 145), mask)
        # Outline border
        draw_fg.rounded_rectangle([(88, 143), (242, 297)], radius=36, outline=(56, 189, 248), width=2)
        
    # Text block
    # Badge
    draw_fg.rounded_rectangle([(275, 125), (460, 155)], radius=15, fill=(30, 58, 138), outline=(56, 189, 248), width=1)
    draw_fg.text((290, 131), "OFFICIAL RESIDENT APP", font=font_badge, fill=(56, 189, 248))
    
    # Main title
    draw_fg.text((275, 170), "Rudra Group PG", font=font_title, fill=(255, 255, 255))
    # Subtitle
    draw_fg.text((275, 235), "Student & Resident Companion Portal", font=font_subtitle, fill=(148, 163, 184))
    
    # Feature pill badges
    pills = ["Room & Bed Allocation", "Digital Rent Ledger", "Sub-Meter Audits", "24x7 Help Desk"]
    cur_x = 275
    for p in pills:
        text_bbox = draw_fg.textbbox((0, 0), p, font=font_pill)
        w = text_bbox[2] - text_bbox[0] + 28
        draw_fg.rounded_rectangle([(cur_x, 305), (cur_x + w, 345)], radius=8, fill=(20, 32, 60), outline=(71, 85, 105), width=1)
        draw_fg.text((cur_x + 14, 316), p, font=font_pill, fill=(226, 232, 240))
        cur_x += w + 12
        if cur_x > 940:
            break
            
    # Bottom security tag
    draw_fg.text((90, 430), "✓ Verified PG Resident Management • Secure Real-Time Host Infrastructure", font=ImageFont.truetype(font_reg_path, 15), fill=(100, 116, 139))
    
    fg_path = os.path.join(output_dir, 'feature_graphic_1024x500.png')
    fg.save(fg_path, 'PNG', optimize=True)
    print(f"Generated Play Store Feature Graphic: {fg_path} ({os.path.getsize(fg_path)} bytes)")

    # -------------------------------------------------------------
    # 3. Phone Screenshots (1080x2400)
    # -------------------------------------------------------------
    screenshots_meta = [
        {
            'filename': '01_resident_portal_login.png',
            'tag': 'SECURE ACCESS',
            'headline': 'Resident Portal Login',
            'subline': 'Instant access with your registered mobile number & default credentials',
            'accent': (2, 132, 199),
            'content_type': 'login'
        },
        {
            'filename': '02_stay_overview_dashboard.png',
            'tag': 'STAY OVERVIEW',
            'headline': 'Room & Bed Allocation',
            'subline': 'Live stay snapshot: Room 201 (2 Sharing AC) & Branch Manager Desk',
            'accent': (16, 185, 129),
            'content_type': 'home'
        },
        {
            'filename': '03_payments_and_dues_ledger.png',
            'tag': 'TRANSPARENT BILLING',
            'headline': 'Payments & Dues Ledger',
            'subline': 'Track monthly rent, security deposit & offline UPI transfers',
            'accent': (245, 158, 11),
            'content_type': 'payments'
        },
        {
            'filename': '04_electricity_submeter_audits.png',
            'tag': 'UTILITY BILLING',
            'headline': 'Sub-Meter Electricity Audits',
            'subline': 'Submit monthly meter photo snapshots & track kWh consumption units',
            'accent': (14, 165, 233),
            'content_type': 'electricity'
        },
        {
            'filename': '05_manager_helpdesk_support.png',
            'tag': 'RESIDENT CARE',
            'headline': 'Direct Support & Help Desk',
            'subline': 'One-tap manager call, WhatsApp support & instant maintenance tickets',
            'accent': (99, 102, 241),
            'content_type': 'support'
        }
    ]
    
    font_sc_tag = ImageFont.truetype(font_bold_path, 28)
    font_sc_h1 = ImageFont.truetype(font_bold_path, 54)
    font_sc_sub = ImageFont.truetype(font_reg_path, 30)
    
    sw, sh = 1080, 2400
    device_w, device_h = 880, 1720
    device_x = (sw - device_w) // 2
    device_y = 560
    
    for item in screenshots_meta:
        canvas = Image.new('RGB', (sw, sh), (15, 23, 42))
        draw_c = ImageDraw.Draw(canvas)
        
        # Background gradient
        for y in range(sh):
            r = int(15 + (y / sh) * 12)
            g = int(23 + (y / sh) * 15)
            b = int(42 + (y / sh) * 25)
            draw_c.line([(0, y), (sw, y)], fill=(r, g, b))
            
        # Top Header Content
        tag_text = item['tag']
        tag_bbox = draw_c.textbbox((0, 0), tag_text, font=font_sc_tag)
        tag_w = tag_bbox[2] - tag_bbox[0] + 36
        draw_c.rounded_rectangle([(device_x, 140), (device_x + tag_w, 196)], radius=28, fill=(30, 41, 59), outline=item['accent'], width=2)
        draw_c.text((device_x + 18, 148), tag_text, font=font_sc_tag, fill=item['accent'])
        
        draw_c.text((device_x, 220), item['headline'], font=font_sc_h1, fill=(255, 255, 255))
        draw_c.text((device_x, 300), item['subline'], font=font_sc_sub, fill=(148, 163, 184))
        
        # Draw Mock Phone Frame
        # Shadow
        shadow_rect = [(device_x - 10, device_y - 10), (device_x + device_w + 10, device_y + device_h + 10)]
        draw_c.rounded_rectangle(shadow_rect, radius=56, fill=(5, 10, 20))
        
        # Outer bezel
        draw_c.rounded_rectangle([(device_x, device_y), (device_x + device_w, device_y + device_h)], radius=50, fill=(24, 32, 54), outline=(71, 85, 105), width=4)
        
        # Inner screen canvas
        screen_margin = 16
        sx1 = device_x + screen_margin
        sy1 = device_y + screen_margin
        sx2 = device_x + device_w - screen_margin
        sy2 = device_y + device_h - screen_margin
        
        screen_img = _generate_screen_mockup(item['content_type'], sx2 - sx1, sy2 - sy1, logo_source)
        canvas.paste(screen_img, (sx1, sy1))
        
        # Dynamic island on top of screen
        island_w, island_h = 190, 42
        ix = sx1 + ((sx2 - sx1) - island_w) // 2
        iy = sy1 + 18
        draw_c.rounded_rectangle([(ix, iy), (ix + island_w, iy + island_h)], radius=21, fill=(15, 23, 42))
        draw_c.ellipse([(ix + 140, iy + 13), (ix + 156, iy + 29)], fill=(30, 41, 59))
        
        out_file = os.path.join(screenshots_dir, item['filename'])
        canvas.save(out_file, 'PNG', optimize=True)
        print(f"Generated Play Store Screenshot: {out_file} ({os.path.getsize(out_file)} bytes)")
        
    print("All Play Store graphics generated successfully!")

def _generate_screen_mockup(content_type, w, h, logo_path):
    screen = Image.new('RGB', (w, h), (248, 250, 252))
    d = ImageDraw.Draw(screen)
    
    font_bold = r'C:\Windows\Fonts\segoeuib.ttf'
    font_reg = r'C:\Windows\Fonts\segoeui.ttf'
    
    f_title = ImageFont.truetype(font_bold, 36)
    f_sub = ImageFont.truetype(font_reg, 22)
    f_body = ImageFont.truetype(font_reg, 20)
    f_bold = ImageFont.truetype(font_bold, 24)
    f_btn = ImageFont.truetype(font_bold, 28)
    
    # Status bar
    d.text((40, 25), "9:41", font=ImageFont.truetype(font_bold, 20), fill=(15, 23, 42))
    
    if content_type == 'login':
        # Header logo
        if os.path.exists(logo_path):
            logo = Image.open(logo_path).convert('RGBA').resize((120, 120), Image.Resampling.LANCZOS)
            screen.paste(logo, (w//2 - 60, 120), logo)
            
        d.text((w//2, 270), "Resident Portal Login", font=f_title, fill=(15, 23, 42), anchor="ms")
        d.text((w//2, 310), "Access your room, ledgers & audits", font=f_sub, fill=(100, 116, 139), anchor="ms")
        
        # Mobile Field
        d.text((60, 400), "Registered Mobile Number", font=f_bold, fill=(30, 41, 59))
        d.rounded_rectangle([(60, 440), (w - 60, 530)], radius=16, fill=(255, 255, 255), outline=(203, 213, 225), width=2)
        d.text((90, 470), "6354351080", font=f_title, fill=(15, 23, 42))
        
        # Password Field
        d.text((60, 580), "Password", font=f_bold, fill=(30, 41, 59))
        d.rounded_rectangle([(60, 620), (w - 60, 710)], radius=16, fill=(255, 255, 255), outline=(203, 213, 225), width=2)
        d.text((90, 650), "••••••••••••", font=f_title, fill=(15, 23, 42))
        
        # Action Row
        d.text((60, 740), "Default Password: password123", font=ImageFont.truetype(font_reg, 18), fill=(100, 116, 139))
        d.text((w - 60, 740), "Forgot Password?", font=ImageFont.truetype(font_bold, 18), fill=(2, 132, 199), anchor="ra")
        
        # Sign In Button
        d.rounded_rectangle([(60, 810), (w - 60, 910)], radius=20, fill=(15, 23, 42))
        d.text((w//2, 860), "→ Sign In to Portal", font=f_btn, fill=(255, 255, 255), anchor="mm")
        
        # QR Registration Card
        d.rounded_rectangle([(60, 960), (w - 60, 1100)], radius=20, fill=(238, 242, 255), outline=(199, 210, 254), width=2)
        d.text((90, 1000), "New Resident Registration?", font=f_bold, fill=(30, 27, 75))
        d.text((90, 1040), "Scan branch reception standee QR code", font=f_sub, fill=(99, 102, 241))
        
    elif content_type == 'home':
        # Hero Card
        d.rounded_rectangle([(40, 100), (w - 40, 480)], radius=26, fill=(15, 23, 42))
        # Badge
        d.rounded_rectangle([(70, 130), (330, 175)], radius=14, fill=(30, 41, 59))
        d.text((90, 142), "ROOM 201 • BED A", font=f_bold, fill=(56, 189, 248))
        d.text((w - 70, 142), "₹6,500/mo", font=f_title, fill=(255, 255, 255), anchor="ra")
        
        d.text((70, 210), "Welcome, Karan Mack 👋", font=ImageFont.truetype(font_bold, 42), fill=(255, 255, 255))
        d.text((70, 270), "Ref: #11 • Joining Date: 01 Aug 2026", font=f_sub, fill=(148, 163, 184))
        
        d.line([(70, 320), (w - 70, 320)], fill=(51, 65, 85), width=2)
        
        # 3 status indicators
        d.text((70, 350), "RENT PAYMENT", font=ImageFont.truetype(font_reg, 16), fill=(148, 163, 184))
        d.text((70, 380), "PAID", font=f_bold, fill=(34, 197, 94))
        
        d.text((320, 350), "DEPOSIT STATUS", font=ImageFont.truetype(font_reg, 16), fill=(148, 163, 184))
        d.text((320, 380), "PAID", font=f_bold, fill=(34, 197, 94))
        
        d.text((580, 350), "KYC STATUS", font=ImageFont.truetype(font_reg, 16), fill=(148, 163, 184))
        d.text((580, 380), "APPROVED", font=f_bold, fill=(34, 197, 94))
        
        # Resident Services Grid
        d.text((40, 520), "Resident Services", font=f_title, fill=(15, 23, 42))
        actions = [("Pay Rent", (2, 132, 199)), ("My Room", (16, 185, 129)), ("Electricity", (245, 158, 11)), ("Support", (99, 102, 241))]
        kw = (w - 80 - 45) // 4
        for i, (name, col) in enumerate(actions):
            kx = 40 + i * (kw + 15)
            d.rounded_rectangle([(kx, 570), (kx + kw, 710)], radius=18, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
            d.ellipse([(kx + kw//2 - 26, 595), (kx + kw//2 + 26, 647)], fill=col)
            d.text((kx + kw//2, 670), name, font=ImageFont.truetype(font_bold, 18), fill=(30, 41, 59), anchor="mm")
            
        # Stay Overview
        d.text((40, 760), "My Stay Overview", font=f_title, fill=(15, 23, 42))
        d.rounded_rectangle([(40, 810), (w - 40, 1120)], radius=24, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        
        rows = [
            ("Branch Address", "Naroda Main Branch (PG-NRD-01)"),
            ("Room Floor & Type", "Room 201 (2 Sharing AC)"),
            ("Branch Manager Desk", "Nareshbhai • 9876543265")
        ]
        for i, (lbl, val) in enumerate(rows):
            ry = 830 + i * 95
            d.text((80, ry), lbl, font=f_sub, fill=(100, 116, 139))
            d.text((w - 80, ry), val, font=f_bold, fill=(15, 23, 42), anchor="ra")
            if i < 2:
                d.line([(80, ry + 65), (w - 80, ry + 65)], fill=(241, 245, 249), width=2)
                
    elif content_type == 'payments':
        # Header Dues Card
        d.rounded_rectangle([(40, 100), (w - 40, 420)], radius=26, fill=(15, 23, 42))
        d.text((70, 140), "TOTAL OUTSTANDING DUES", font=ImageFont.truetype(font_bold, 20), fill=(148, 163, 184))
        d.rounded_rectangle([(w - 280, 130), (w - 70, 175)], radius=14, fill=(20, 83, 45))
        d.text((w - 175, 143), "✓ ALL DUES CLEARED", font=ImageFont.truetype(font_bold, 16), fill=(74, 222, 128), anchor="mm")
        
        d.text((70, 200), "₹0.00", font=ImageFont.truetype(font_bold, 64), fill=(255, 255, 255))
        d.text((70, 310), "All rent and security deposit dues are completely cleared.", font=f_sub, fill=(148, 163, 184))
        
        # Dues Breakdown Grid
        d.text((40, 460), "Dues Breakdown", font=f_title, fill=(15, 23, 42))
        cards = [
            ("Monthly Rent", "₹6500", "Paid", (34, 197, 94)),
            ("Security Deposit", "₹10000", "Paid", (34, 197, 94)),
            ("Electricity Meter", "As Consumed", "Meter Audit", (2, 132, 199)),
            ("Payable Today", "₹0.00", "Clear", (34, 197, 94))
        ]
        cw = (w - 80 - 20) // 2
        for idx, (title, amt, badge, bcol) in enumerate(cards):
            col = idx % 2
            row = idx // 2
            cx = 40 + col * (cw + 20)
            cy = 510 + row * 180
            d.rounded_rectangle([(cx, cy), (cx + cw, cy + 160)], radius=20, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
            d.text((cx + 20, cy + 24), title, font=f_sub, fill=(100, 116, 139))
            d.rounded_rectangle([(cx + cw - 90, cy + 20), (cx + cw - 20, cy + 54)], radius=10, fill=(240, 253, 244))
            d.text((cx + cw - 55, cy + 30), badge, font=ImageFont.truetype(font_bold, 14), fill=bcol, anchor="mm")
            d.text((cx + 20, cy + 80), amt, font=f_title, fill=(15, 23, 42))
            
        # Offline Payment Proof
        d.text((40, 910), "Offline / P2P Payment Proof", font=f_title, fill=(15, 23, 42))
        d.rounded_rectangle([(40, 960), (w - 40, 1180)], radius=20, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        d.text((80, 990), "Branch UPI Handle", font=f_bold, fill=(15, 23, 42))
        d.text((80, 1030), "rudra.naroda@upi • GPay / PhonePe", font=f_sub, fill=(100, 116, 139))
        d.rounded_rectangle([(80, 1080), (w - 80, 1150)], radius=14, fill=(238, 242, 255), outline=(199, 210, 254), width=1)
        d.text((w//2, 1115), "☁ Upload Payment Proof Screenshot", font=f_bold, fill=(99, 102, 241), anchor="mm")
        
    elif content_type == 'electricity':
        # Submeter Overview Card
        d.rounded_rectangle([(40, 100), (w - 40, 360)], radius=26, fill=(15, 23, 42))
        d.text((70, 140), "SUB-METER OVERVIEW", font=ImageFont.truetype(font_bold, 20), fill=(148, 163, 184))
        d.text((70, 190), "Room 201 Meter", font=ImageFont.truetype(font_bold, 48), fill=(255, 255, 255))
        d.text((70, 280), "Naroda Main Branch • Meter #MTR-201", font=f_sub, fill=(148, 163, 184))
        
        # Submit Reading Card
        d.text((40, 400), "Submit Monthly Reading", font=f_title, fill=(15, 23, 42))
        d.rounded_rectangle([(40, 450), (w - 40, 820)], radius=24, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        
        d.text((70, 480), "Current Sub-Meter Reading (kWh)", font=f_sub, fill=(100, 116, 139))
        d.rounded_rectangle([(70, 520), (w - 70, 600)], radius=16, fill=(248, 250, 252), outline=(203, 213, 225), width=2)
        d.text((100, 545), "1420.5 kWh", font=f_title, fill=(15, 23, 42))
        
        # Photo attachment
        d.rounded_rectangle([(70, 630), (w - 70, 710)], radius=16, fill=(240, 253, 244), outline=(134, 239, 172), width=1)
        d.text((100, 655), "✓ Physical Meter Snapshot Attached", font=f_bold, fill=(22, 101, 52))
        
        d.rounded_rectangle([(70, 735), (w - 70, 805)], radius=16, fill=(15, 23, 42))
        d.text((w//2, 770), "Submit Reading for Audit", font=f_bold, fill=(255, 255, 255), anchor="mm")
        
        # History
        d.text((40, 860), "Meter Reading History", font=f_title, fill=(15, 23, 42))
        readings = [
            ("August 2026", "1380 kWh (65 Units)", "₹650", "APPROVED"),
            ("July 2026", "1315 kWh (72 Units)", "₹720", "APPROVED")
        ]
        for idx, (m, units, amt, st) in enumerate(readings):
            ry = 910 + idx * 130
            d.rounded_rectangle([(40, ry), (w - 40, ry + 110)], radius=18, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
            d.text((70, ry + 22), m, font=f_bold, fill=(15, 23, 42))
            d.text((70, ry + 60), units, font=f_sub, fill=(100, 116, 139))
            d.text((w - 70, ry + 22), amt, font=f_bold, fill=(22, 101, 52), anchor="ra")
            d.text((w - 70, ry + 60), st, font=ImageFont.truetype(font_bold, 14), fill=(34, 197, 94), anchor="ra")
            
    elif content_type == 'support':
        # Direct manager card
        d.text((40, 100), "Direct Manager Contact", font=f_title, fill=(15, 23, 42))
        
        # 2 action cards side by side
        cw = (w - 80 - 20) // 2
        d.rounded_rectangle([(40, 150), (40 + cw, 330)], radius=20, fill=(240, 253, 244), outline=(187, 247, 208), width=2)
        d.text((40 + cw//2, 210), "📞 Call Manager", font=f_title, fill=(22, 101, 52), anchor="mm")
        d.text((40 + cw//2, 260), "Direct Line to Reception", font=f_sub, fill=(74, 222, 128), anchor="mm")
        
        d.rounded_rectangle([(40 + cw + 20, 150), (w - 40, 330)], radius=20, fill=(238, 242, 255), outline=(199, 210, 254), width=2)
        d.text((40 + cw + 20 + cw//2, 210), "💬 WhatsApp Support", font=f_title, fill=(67, 56, 202), anchor="mm")
        d.text((40 + cw + 20 + cw//2, 260), "Quick Chat Help Desk", font=f_sub, fill=(99, 102, 241), anchor="mm")
        
        # Raise Ticket
        d.text((40, 370), "Raise Maintenance Request", font=f_title, fill=(15, 23, 42))
        d.rounded_rectangle([(40, 420), (w - 40, 770)], radius=24, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        
        d.text((70, 450), "Category: Plumbing & Geyser", font=f_bold, fill=(30, 41, 59))
        d.rounded_rectangle([(70, 490), (w - 70, 560)], radius=14, fill=(248, 250, 252), outline=(203, 213, 225), width=2)
        d.text((90, 510), "Geyser water heater not turning on", font=f_sub, fill=(15, 23, 42))
        
        d.rounded_rectangle([(70, 580), (w - 70, 680)], radius=14, fill=(248, 250, 252), outline=(203, 213, 225), width=2)
        d.text((90, 600), "Geyser in room 201 bathroom is cold...", font=f_sub, fill=(100, 116, 139))
        
        d.rounded_rectangle([(70, 700), (w - 70, 755)], radius=14, fill=(15, 23, 42))
        d.text((w//2, 728), "Submit Ticket to Branch Manager", font=f_bold, fill=(255, 255, 255), anchor="mm")
        
        # Ticket history
        d.text((40, 810), "My Active Tickets", font=f_title, fill=(15, 23, 42))
        d.rounded_rectangle([(40, 860), (w - 40, 1040)], radius=20, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        d.text((70, 890), "TCK-2026-081 • PLUMBING", font=f_bold, fill=(15, 23, 42))
        d.rounded_rectangle([(w - 180, 880), (w - 70, 920)], radius=10, fill=(240, 253, 244))
        d.text((w - 125, 892), "RESOLVED", font=ImageFont.truetype(font_bold, 14), fill=(22, 101, 52), anchor="mm")
        d.text((70, 940), "Bathroom tap replaced by maintenance staff", font=f_sub, fill=(100, 116, 139))
        d.text((70, 980), "Resolution Remarks: Completed on 08 Sep 2026 ✓", font=ImageFont.truetype(font_bold, 18), fill=(22, 101, 52))
        
    return screen

if __name__ == '__main__':
    create_play_store_assets()
