# Document 03: Flutter Student Mobile Application Specification

## 1. Application Vision & UI/UX Principles

The **Rudra Group PG Student Mobile App** is a resident companion application designed specifically for students who have physically visited a PG property. It is designed using **Material Design 3**, focusing on elegance, clarity, soft visual cues, and intuitive navigation.

---

## 2. Brand Guidelines & Design System

### Color Palette (Tokens)
```
Primary        #0F172A  (Deep Slate Navy - Main headers, primary actions)
Secondary      #2563EB  (Royal Blue - Interactive highlights, active tabs)
Accent         #14B8A6  (Teal - Key highlights, badges, success states)
Background     #F8FAFC  (Soft Off-White - Smooth canvas)
Card           #FFFFFF  (Pure White - Elevated container blocks)
Success        #16A34A  (Vibrant Green - Approved, Available, Paid)
Warning        #F59E0B  (Amber - Pending, Reserved, Verification)
Error          #DC2626  (Crimson Red - Occupied, Rejected, Overdue)
```

### Typography System
- **Font Family**: `Poppins` (Google Fonts)
- **Scale**:
  - `Display Large`: 28pt Bold (`#0F172A`)
  - `Title Large`: 20pt SemiBold (`#0F172A`)
  - `Body Medium`: 14pt Regular (`#475569`)
  - `Caption`: 12pt Medium (`#64748B`)

### Styling & Component Tokens
- **Border Radius**: `16.0` (Cards & Dialogs), `12.0` (Buttons & Form Fields)
- **Elevation / Shadows**: Soft Ambient Shadows (`BoxShadow(color: Color(0x0F000000), blurRadius: 10, offset: Offset(0, 4))`)
- **Spacing Scale**: 8px, 16px, 24px, 32px consistent padding.

---

## 3. Screen Specifications (18 Screens)

### Screen 1: Splash Screen
- **Purpose**: App launch & branding.
- **Components**: Centered animated logo, "Rudra Group PG" text, "Premium Living Experience" tagline.
- **Behavior**: 2-second timer then auto-navigates to Welcome Screen (or Resident Dashboard if authenticated).

### Screen 2: Welcome Screen
- **Purpose**: Introduce value proposition to first-time physical visitors.
- **Components**: Feature highlights (Digital Room Allocation, Easy Rent Payment, Monthly Meter Submission).
- **Primary CTA**: "Scan QR Code & Continue".

### Screen 3: QR Branch Detection Screen
- **Purpose**: Simulates branch lock via QR payload scanning.
- **Components**: Simulated branch banner, "Detected Branch: Naroda Branch", branch manager contact badge, address summary.
- **Behavior**: Locks state to the detected branch; user cannot manually switch branches.

### Screen 4: Branch Overview Screen
- **Purpose**: Show complete branch details to the visitor.
- **Components**: Hero image carousel, Amenity grid (High-speed Wi-Fi, RO Water, CCTV Security, Daily Housekeeping, Laundry), Branch Rules, Emergency Contact card.
- **CTA**: "Explore Available Rooms".

### Screen 5: Room Listing Screen
- **Purpose**: Display rooms within the scanned branch.
- **Components**: Category filter pills (All, 2 Sharing, 3 Sharing, AC, Non-AC), Room Cards showing thumbnail image, room number, floor, rent amount, security deposit, and available bed count.

### Screen 6: Room Details Screen
- **Purpose**: Deep dive into specific room features.
- **Components**: Image slider, Sharing capacity indicator, Included furniture tags (Bed, Study Table, Wardrobe), Attached washroom badge, AC indicator.
- **Primary CTA**: "Select Your Bed".

### Screen 7: Interactive Bed Selection Screen (Primary UI Feature)
- **Purpose**: Visual grid layout representing physical room bed placement.
- **Visual Design**:
  - Color Legend: Green = Available, Amber = Reserved, Red = Occupied, Blue = Selected.
  - Interactive grid: Tapping an available bed triggers visual selection feedback (Blue border + tick badge).
- **Primary CTA**: "Proceed with Selected Bed (Bed 2B)".

### Screen 8: Student Registration & KYC Screen
- **Purpose**: Collect resident details after bed selection.
- **Fields**: Full Name, Mobile Number, Aadhaar Number, PAN Number.
- **Upload Widgets**: Aadhaar Front Image, Aadhaar Back Image, PAN Card Image picker components.
- **CTA**: "Submit Booking Request".

### Screen 9: Booking Submitted & Confirmation Screen
- **Purpose**: Confirmation state following registration.
- **Components**: Success illustration, Booking Reference ID (e.g. `BK-2026-8812`), Selected Branch, Room & Bed summary, "Pending Sub Admin Approval" status pill.
- **CTA**: "Go to Dashboard Status".

### Screen 10: Resident Dashboard Screen (Main Home Tab)
- **Purpose**: Primary hub for active residents post-approval.
- **Components**:
  - Header: Resident Greeting + Active Branch Badge.
  - Quick Info Cards: My Room & Bed info, Next Rent Due Date, Outstanding Balance.
  - Quick Action Buttons: Pay Rent, Submit Meter Reading, Raise Complaint.

### Screen 11: My Room Screen
- **Purpose**: Complete detail view of resident's current room assignment.
- **Components**: Branch Name, Floor Number, Room Number, Bed Code, Monthly Rent, Security Deposit held, Roommate list (if applicable).

### Screen 12: Payments Screen (Payments Tab)
- **Purpose**: Financial ledger and P2P payment submission.
- **Components**:
  - Dues Summary: Monthly Rent Due, Electricity Bill Due, Security Deposit Paid.
  - Payment Method Options: Display PG Bank UPI ID & QR Code for scanning.
  - Proof Submission Form: Mode selection (Cash / UPI / Bank), Transaction UTR Number input, Screenshot File Picker.
  - Payment History List with verification status tags (Verified / Pending / Rejected).

### Screen 13: Electricity Submission Module
- **Purpose**: Monthly meter reading submission.
- **Components**:
  - Previous Month Final Reading display.
  - Meter Reading Number Input field.
  - Camera Attachment Button for live meter photo upload.
  - Submission History Timeline showing past unit consumption and billed amounts.

### Screen 14: Notifications Screen (Notifications Tab)
- **Purpose**: Announcements and alert logs.
- **Components**: Categorized list items (Rent Reminders, Payment Verification confirmations, Electricity Bills, Branch Announcements).

### Screen 15: Profile Screen (Profile Tab)
- **Purpose**: Resident identity and settings hub.
- **Components**: Avatar photo, Full Name, Mobile Number, Emergency Contact details, Quick links to Documents, Support, Settings.

### Screen 16: Documents Screen
- **Purpose**: Vault for verified identity documents and payment receipts.
- **Components**: Document cards with preview thumbnails for Aadhaar Card, PAN Card, Deposit Receipt, and Monthly Rent Receipts.

### Screen 17: Support & Complaints Screen
- **Purpose**: Resident assistance portal.
- **Components**: Quick Call Sub Admin button, WhatsApp Chat shortcut, "Raise Complaint" ticket modal, FAQ expanders.

### Screen 18: Settings Screen
- **Purpose**: App preferences and legal policies.
- **Components**: Push notification toggles, Terms of Service, Privacy Policy, App Version (`v1.0.0`), Logout button.

---

## 4. Bottom Navigation Structure

The app strictly uses a 4-tab Bottom Navigation Bar (No side drawer):

```
┌────────────────────────────────────────────────────────┐
│  [ Home ]      [ Payments ]     [ Alerts ]   [ Profile]│
└────────────────────────────────────────────────────────┘
```
- **Home**: Resident Dashboard (or Branch Explorer if unapproved).
- **Payments**: Payment Ledger & Proof Submission.
- **Alerts**: Notifications & Announcements.
- **Profile**: Resident Profile, KYC, & Support.

---

## 5. Recommended Flutter Code Structure

```
lib/
├── main.dart
├── core/
│   ├── constants/        # AppColors, AppTypography, AppAssets
│   ├── theme/            # AppTheme (Material 3 configuration)
│   └── utils/            # Formatters, Validators
│
├── shared/
│   └── widgets/          # CustomButton, CustomCard, CustomTextField, StatusBadge
│
└── features/
    ├── onboarding/       # Splash, Welcome, QR Detection
    ├── booking/          # BranchOverview, RoomListing, RoomDetails, BedSelection, Registration
    ├── resident/         # Dashboard, MyRoom, Profile, Documents
    ├── payments/         # PaymentsScreen, ProofUploadModal
    ├── electricity/      # ElectricityForm, ReadingHistory
    └── support/          # SupportScreen, ComplaintModal
```
