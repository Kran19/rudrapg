# Google Play Console — Store Listing & Publication Kit

**App Name:** Rudra PG - Resident Companion  
**Package Name (Application ID):** `com.rudrapg.app`  
**Version:** `1.0.2` (Version Code: `3`)  
**Target SDK:** 36 (Android 15) | **Min SDK:** 24 (Android 7.0)  

---

## 1. Quick Asset Access Directory

All files have been prepared and mirrored to your local system for 1-click drag & drop:

| Asset | Project File Path | Direct Downloads Path | Specs / Status |
| :--- | :--- | :--- | :--- |
| **Android App Bundle (.aab)** | `student/play_store_assets/rudraboyspg-release.aab` | `D:\Downloads\play_store_assets\rudraboyspg-release.aab` | **63.0 MB** (Signed with Release Keystore) |
| **Play Store App Icon** | `student/play_store_assets/app_icon_512x512.png` | `D:\Downloads\play_store_assets\app_icon_512x512.png` | **512 x 512 px**, 32-bit PNG |
| **Feature Graphic Banner** | `student/play_store_assets/feature_graphic_1024x500.png` | `D:\Downloads\play_store_assets\feature_graphic_1024x500.png` | **1024 x 500 px**, PNG (No alpha) |
| **Phone Screenshots (5)** | `student/play_store_assets/screenshots/` | `D:\Downloads\play_store_assets\screenshots/` | **1080 x 2400 px** (20:9 Ultra-HD) |

---

## 2. Store Listing Metadata (Copy & Paste Ready)

### App Title (29 / 30 Characters)
```text
Rudra PG - Resident Companion
```

### Short Description (78 / 80 Characters)
```text
Official resident companion for room allocation, digital rent & meter audits.
```

### Full Description (Play Store Formatted)
```text
Welcome to the official Rudra PG Resident Companion App — the all-in-one digital portal designed exclusively for verified students and residents staying across Rudra Group PG properties.

Manage your entire stay experience from move-in to move-out directly from your smartphone with total transparency, secure records, and instant warden assistance.

KEY FEATURES FOR RESIDENTS:

🏢 ROOM & BED ALLOCATION OVERVIEW
• Instant access to your assigned branch, room number, floor, and designated bed.
• View roommate roster, check-in dates, and security deposit records in real time.
• Full details on stay terms and amenity allocations.

💳 DIGITAL RENT LEDGER & PAYMENT RECEIPTS
• Clear, transparent overview of monthly rent balances, dues, and advance payments.
• Itemized transaction history with downloadable PDF payment receipts.
• Instant notification when rent payments or security deposits are acknowledged by management.

⚡ TRANSPARENT ELECTRICITY SUB-METER AUDITS
• End-to-end room sub-meter tracking to eliminate billing discrepancies.
• Transparent monthly unit consumption, tariff breakdown, and roommate split calculations.
• Access historical meter audit logs with photographic proof uploaded by staff.

🛠️ 24x7 MAINTENANCE & HELPDESK TICKETING
• Lodge maintenance requests for plumbing, electrical, WiFi, carpentry, or housekeeping directly in seconds.
• Attach photo proof to your complaint for immediate warden dispatch.
• Real-time ticket progress updates: Open → In Progress → Resolved.

🔒 SECURE, VERIFIED AUTHENTICATION
• Password and OTP-verified access strictly limited to registered mobile numbers.
• Resident data is encrypted in transit over HTTPS/TLS protocols.
• Full resident profile management with in-app data export and account control.

---
SUPPORT & INQUIRIES:
For onboarding support or stay inquiries, reach out to our dedicated property administration team:
• Property: Rudra Boys PG, Ahmedabad, Gujarat
• Email: contact@rudrapg.com
```

---

## 3. Store Categorization & Tags

- **Application Category:** House & Home *(Alternative: Lifestyle)*
- **Content Rating:** Everyone (3+)
- **Tags (Select up to 5):**
  1. `Property Management`
  2. `Housing`
  3. `Utilities`
  4. `Bill Payments`
  5. `Hostel Management`

---

## 4. Keystore & App Signing Security Specifications

The release bundle was signed using an industry-standard PKCS12 release keystore. Keep these credentials confidential and securely backed up:

| Property | Value |
| :--- | :--- |
| **Keystore File Location** | `student/android/app/upload-keystore.jks` |
| **Keystore Type** | `PKCS12` |
| **Key Alias** | `upload` |
| **Store / Key Password** | `rudrapg@2026` |
| **Validity** | Until **January 31, 2054** |
| **Certificate SHA-1** | `41:0D:B7:90:B7:C0:E5:EB:1C:03:C5:87:14:B7:59:BE:6B:B0:0B:50` |
| **Certificate SHA-256** | `06:5F:E4:D1:97:41:42:BC:D0:A8:74:39:6C:6F:6E:0C:21:57:F1:6F:79:C7:D0:6D:6A:E0:8E:84:0B:29:B4:F6` |

> [!IMPORTANT]
> Google Play Console uses **Play App Signing**. When you upload your first `.aab`, Google registers the upload key above, verifies its SHA-1/SHA-256, and signs public distribution APKs with Google's managed key.

---

## 5. Google Play Console — Data Safety Declarations

Complete the **Data safety questionnaire** in the Play Console with the following responses:

1. **Does your app collect or share any of the required user data types?**  
   👉 **Yes**
2. **Is all of the user data collected by your app encrypted in transit?**  
   👉 **Yes** (All API communication uses HTTPS / TLS 1.3).
3. **Do you provide a way for users to request that their data be deleted?**  
   👉 **Yes**  
   - **Delete Account URL (Web Link):**  
     `https://emperorsmartsolutions.com/rudrapgwebsite/delete-account`  
     *(Alternative Live URL on Privacy Policy: `https://emperorsmartsolutions.com/rudrapgwebsite/privacy-policy#account-deletion`)*  
   - In-app account deletion is also available under *Settings > Delete Resident Account*.
4. **Data Types Collected:**
   - **Personal info:**
     - *Name* (App functionality, Account management)
     - *Phone number* (Account verification, login identifier)
     - *Email address* (Optional / Receipts)
   - **Financial info:**
     - *Purchase / Payment history* (Displaying rent dues, transaction receipts)
   - **Photos & Videos:**
     - *Photos* (Optional user profile avatar & maintenance ticket attachments)
5. **Data Sharing:**  
   👉 **No** (User data is never sold or shared with third-party advertising or marketing brokers).

---

## 6. Step-by-Step Google Play Console Release Flow

1. **Log in to Google Play Console:**
   - Go to [Google Play Console](https://play.google.com/console).
   - Select your developer account.
2. **Create or Open App:**
   - Click **Create app**.
   - App name: `Rudra PG - Resident Companion`
   - Default language: English (United States) or English (India)
   - App or game: **App**
   - Free or paid: **Free**
3. **Set up Store Listing:**
   - Navigate to **Grow** → **Store presence** → **Main store listing**.
   - Paste the **Title**, **Short description**, and **Full description** from Section 2 above.
   - Upload `app_icon_512x512.png` into the **App icon** field.
   - Upload `feature_graphic_1024x500.png` into the **Feature graphic** field.
   - Upload the 5 screenshot PNG files from `screenshots/` into **Phone screenshots**.
   - Click **Save**.
4. **Complete Policy & App Content:**
   - Complete **Privacy policy**: `https://emperorsmartsolutions.com/rudrapgwebsite/privacy-policy`
   - Complete **App access** (Select *"All or some functionality is restricted"* and provide resident demo login: Mobile: `6354351080`, Password: `Password@123`).
   - Complete **Ads** (Select *"No, my app does not contain ads"*).
   - Complete **Content rating** (Select questionnaire, answers are all No/None -> Rating is Everyone 3+).
   - Complete **Target audience** (Select 18 and over).
   - Complete **Data safety** using Section 5 above.
   - Complete **Government apps** (Select *"No"*).
   - Complete **Financial features** (Select *"Personal finances management"* or *"My app doesn't provide financial features"*).
5. **Create Production / Closed Testing Release:**
   - Navigate to **Release** → **Production** (or **Testing** → **Closed testing**).
   - Click **Create new release**.
   - Under **App bundles**, drag & drop `rudraboyspg-release.aab`.
   - Release name will automatically populate: `1.0.1 (2)`.
   - Release notes:
     ```text
     Initial release of Rudra PG Resident Companion app.
     - View room and bed allocation details.
     - View monthly rent ledgers and payment receipts.
     - Monitor electricity sub-meter readings.
     - 24x7 help desk and maintenance request ticketing.
     ```
   - Click **Next** → **Review and roll out release**!
