# RudraboysPG (iOS) — Apple App Store Rejection Resolution & Resubmission Master Guide

**Document Version:** 1.0  
**Target App:** RudraboysPG (Apple App ID: 6808289005)  
**Submission ID:** 578a1932-0033-4078-8729-8e897652a43b  
**Rejected Build:** 1.0.1 (1)  
**Target Resubmission Build:** 1.0.1 (2)  
**Primary Guideline:** Guideline 2.1.0 Performance: App Completeness (Information Needed - New App Submission)  

---

## 1. Executive Summary & Objective

Apple App Review has paused review under **Guideline 2.1 - Information Needed**. Because this Apple Developer account has a limited submission history, Apple requires supplementary verification to ensure the app is complete, operates reliably on physical devices, and provides transparent reviewer access.

Additionally, build `1.0.1 (1)` had layout overflow issues (`RIGHT OVERFLOWED BY X PIXELS`) on small device screens and unconstrained rows. These code defects have been resolved in the codebase, and `pubspec.yaml` has been incremented to **`1.0.1+2`**.

This document provides the developer with exact, step-by-step instructions to:
1. Compile and upload the new build **`1.0.1 (2)`** to App Store Connect.
2. Swap the rejected build for the new build in the App Store Connect submission.
3. Capture the required physical device screen recording.
4. Submit the official, copy-paste ready technical response to Apple App Review.

---

## 2. Codebase Status & What Has Been Fixed

All horizontal `RenderFlex` overflow errors have been eliminated across all screens using proportional flex constraints and single-line text truncation:

| Component / Screen | Code Fix Details |
| :--- | :--- |
| **`lib/features/login/login_screen.dart`** | Password hint wrapped in `Expanded` with single-line ellipsis; `TextButton` touch padding compressed to prevent the 17px overflow. |
| **`lib/features/home/home_dashboard_screen.dart`** | `_buildOverviewRow` refactored with `Flexible(flex: 2)` for labels and `Expanded(flex: 3)` for values; Stay status indicators divided equally ($33.3\%$ width) using `Expanded`. |
| **`lib/features/payments/payments_screen.dart`** | Total Dues card header configured with `flex: 3` on title and `flex: 2` on badge container; UPI handle and proof upload cards constrained. |
| **`lib/features/resident/my_room_screen.dart`** | Assigned spot badge and rent indicators wrapped in `Flexible` with text truncation. |
| **`lib/features/notifications/notifications_screen.dart`** | Notice category badge and timestamp date row enclosed in `Flexible` with text truncation. |
| **`lib/features/profile/profile_screen.dart`** | Resident full name, branch title, and Sign Out button row constrained with `Flexible`. |
| **`lib/features/settings/settings_screen.dart`** | Sign Out row and Delete Resident Account modal title wrapped in `Flexible` / `Expanded`. |
| **`lib/core/widgets/section_header.dart`** | Shared section header title wrapped in `Expanded` to protect all section titles globally. |
| **`pubspec.yaml`** | Version updated from `1.0.1+1` to `1.0.1+2`. |

> **Static Analysis Result:** Verified via `dart analyze lib` with **0 errors, 0 warnings, and 0 linter issues**.

---

## 3. Developer Build & Upload Instructions

The developer must generate and upload a new iOS archive (`1.0.1+2`) to replace the rejected build `1.0.1 (1)`.

### Step 3.1: Clean and Fetch Dependencies
Open terminal in the `student/` directory:
```bash
flutter clean
flutter pub get
```

### Step 3.2: Verify Version in `pubspec.yaml`
Confirm that line 19 of `pubspec.yaml` reads:
```yaml
version: 1.0.1+2
```

### Step 3.3: Build iOS Archive
#### Option A (Command Line):
```bash
flutter build ipa --release
```
The output `.ipa` file will be generated at:
`build/ios/ipa/rudraboyspg.ipa`

#### Option B (Xcode GUI):
1. Open the `ios/` folder in Xcode:
   ```bash
   open ios/Runner.xcworkspace
   ```
2. In Xcode, select **Any iOS Device (arm64)** as the target.
3. From the top menu, select **Product** $\rightarrow$ **Archive**.
4. When the Organizer window appears, select the latest archive and click **Distribute App**.
5. Choose **App Store Connect** $\rightarrow$ **Upload** $\rightarrow$ complete the wizard with automatic signing.

### Step 3.4: Wait for Apple Processing
1. Log in to [App Store Connect](https://appstoreconnect.apple.com/apps/6808289005/testflight).
2. Check the **TestFlight** tab until build **`1.0.1 (2)`** shows status **Ready to Submit** (usually takes 5–10 minutes).
3. If prompted for **Missing Compliance** (Encryption), select **No** (Standard HTTPS only) and save.

---

## 4. Physical Device Screen Recording Requirements

Apple requires a video recorded on a physical iPhone showing the actual app running.

### 4.1 Video Specifications
- **Device:** Physical iPhone running iOS 16, 17, or 18 (do not use a simulator).
- **Format:** `.mp4` or `.mov` (portrait orientation).
- **Duration:** 1 to 2 minutes.

### 4.2 Required Video Flow (Must Include These Scenes):
1. **Scene 1: App Launch & Login**
   - Tap the RudraboysPG app icon on the home screen.
   - On the Welcome/Scanner screen, tap **Login** (or **Already an Approved Resident? Log In**).
   - Enter credentials:
     - **Mobile Number:** `6354351080`
     - **Password:** `password123`
   - Tap **Sign In to Portal**.
2. **Scene 2: Dashboard & Stay Details**
   - Show the hero card displaying resident details (**Welcome, Karan Mack**, Room 201).
   - Scroll through **Resident Services** and **My Stay Overview**.
3. **Scene 3: Payments Ledger**
   - Tap the **Payments** tab in bottom navigation.
   - Show the Total Outstanding Dues card, Dues Breakdown, and Branch UPI details.
4. **Scene 4: Support / My Room**
   - Tap **My Room** to show the assigned bed details.
   - Tap **Support** to show the maintenance request desk.
5. **Scene 5: Account Deletion Flow (Mandatory under Guideline 5.1.1(v))**
   - Tap the **Profile** tab in bottom navigation.
   - Tap **App Settings & Policy**.
   - Tap **Delete Resident Account**.
   - Show the confirmation alert dialog displaying the warning prompt. Tap **Cancel**.
   - Tap **Sign Out** to return to the login screen.

---

## 5. App Store Connect Configuration Steps

Follow these exact steps in App Store Connect:

### Step 5.1: Swap the Build
1. Go to **Apps** $\rightarrow$ **RudraboysPG** $\rightarrow$ **Distribution** $\rightarrow$ select **iOS App 1.0** (currently in `Rejected` state).
2. Scroll down to the **Build** section.
3. Hover over the old rejected build **`1.0.1 (1)`** and click the red minus (`—`) button to remove it.
4. Click the **`+` (Add Build)** button.
5. Select the new build: **`1.0.1 (2)`** and click **Done**.
6. Click **Save** in the top-right corner.

### Step 5.2: Update App Review Information Section
On the same page, scroll down to **App Review Information**:
- [x] **Sign-in required** (Ensure checkbox is checked)
- **User name:** `6354351080`
- **Password:** `password123`
- **Notes Field:** Paste the text from **Section 6** below.
- Click **Save**.

---

## 6. Official Reply Text for Apple App Review

Go to the **App Review** tab (where the rejection message is shown), click **Reply to App Review**, attach your screen recording file, and paste the exact text below:

```markdown
Dear Apple App Review Team,

Thank you for your guidance. We have updated our build to 1.0.1 (2) with complete layout stabilization and provided full reviewer information below to fulfill all requirements under Guideline 2.1.

======================================================================
1. PHYSICAL DEVICE SCREEN RECORDING
======================================================================
We have attached a physical iPhone screen recording demonstrating the core resident lifecycle:
- Cold Launch & Welcome / Login Gate
- Sign-In with Demo Resident Credentials
- Home Dashboard, Room Details, and Stay Overview
- Payments & Dues Ledger (Physical hostel room rent and deposit records)
- Support Desk & Maintenance Ticket submission
- Profile Settings and the Account Deletion Flow (Settings > "Delete Resident Account")

The video is attached directly to this reply.

======================================================================
2. APP PURPOSE & TARGET AUDIENCE
======================================================================
- Purpose: RudraboysPG is a resident companion application created exclusively for students and working professionals residing in physical hostel properties operated by Rudra Group PG (located in Ahmedabad/Gujarat, India).
- Problem Solved: It replaces manual paper record-keeping, offline payment receipts, physical meter reading slips, and physical complaint books.
- Value Provided: Residents can monitor their assigned room and bed allocation, review their physical room rent ledger, track electricity sub-meter readings, submit maintenance requests to their branch manager, and receive branch notices in real-time.

======================================================================
3. STEP-BY-STEP REVIEWER INSTRUCTIONS & DEMO CREDENTIALS
======================================================================
Please use the following active pre-configured demo credentials:
- Mobile Number: 6354351080
- Password: password123

Reviewer Navigation Flow:
1. Open the application. If the Branch QR Scanner gate appears on first launch, tap "Login" or "Already an Approved Resident? Log In" at the top/bottom.
2. On the Resident Portal Login screen, enter Mobile Number: 6354351080 and Password: password123, then tap "Sign In to Portal".
3. The resident dashboard will load displaying an active stay (Room 201, 2 Sharing AC), payment status, and quick services.
4. Tap the "Payments" tab in the bottom navigation bar to review the rent breakdown and ledger.
5. Tap "Profile" > "App Settings & Policy" > "Delete Resident Account" to verify the account deletion confirmation flow as required by Guideline 5.1.1(v).

Note regarding Branch QR Registration:
New resident registration is initiated by scanning a physical QR code posted at the PG reception desk. If you wish to test the live scanner, a sample Branch QR Code image is also attached to this submission.

======================================================================
4. EXTERNAL SERVICES & PLATFORMS
======================================================================
The app uses the following external services:
- Backend REST API: Secure HTTPS endpoints (Laravel / PostgreSQL) for resident records, room allocation, and billing ledgers.
- Camera / Photo Gallery (Permission-Based): Used solely for residents to upload KYC documents (Aadhaar/PAN), capture physical electricity sub-meter photos, and submit payment receipt screenshots.
- The app contains no third-party advertisements, tracking SDKs, social login providers, or AI services.

======================================================================
5. REGIONAL DIFFERENCES
======================================================================
The application functions uniformly across all geographic regions worldwide without restrictions. While the physical hostel buildings are situated in Gujarat, India (using INR ₹ currency formatting), all app features and APIs are globally accessible.

======================================================================
6. REGULATION & PHYSICAL GOODS/SERVICES EXEMPTION
======================================================================
- Real-World Physical Accommodation: The payments and dues displayed in the app represent real-world physical hostel accommodation, security deposits, and utility consumption for brick-and-mortar housing. Under Apple Review Guideline 3.1.5(a) and 3.1.3(e), physical goods and services are paid through external banking/UPI mechanisms and do not use Apple In-App Purchases (IAP).
- Proprietary Rights: "Rudra Group PG" and all associated branding belong directly to our organization. No third-party intellectual property is used.

We have attached the demonstration video and replaced the submission build with build 1.0.1 (2). We appreciate your assistance in making RudraboysPG available on the App Store.

Sincerely,  
Emperor Solutions / RudraboysPG Team
```

---

## 7. Final Resubmission Checklist

Before clicking **Resubmit to App Review**, verify:

- [ ] New archive compiled with `version: 1.0.1+2` and uploaded to App Store Connect.
- [ ] In App Store Connect, old build `1.0.1 (1)` removed and build `1.0.1 (2)` selected.
- [ ] Screen recording (`.mov` or `.mp4`) attached in the **Reply to App Review** section.
- [ ] (Optional) Sample Branch QR Code image attached.
- [ ] Full text from **Section 6** pasted into the **Reply to App Review** chat box.
- [ ] Same text pasted into **App Review Information** $\rightarrow$ **Notes** field on the version page.
- [ ] User credentials set to `6354351080` / `password123` in **App Review Information**.
- [ ] Clicked **Submit / Send** on the reply.
- [ ] Clicked **Resubmit to App Review** button on the main submission page.
