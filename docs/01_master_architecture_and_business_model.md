# Document 01: Master Architecture & Business Model

## 1. System Vision & Business Overview

**Rudra Group PG** is a multi-branch Paying Guest (PG) management platform designed to streamline operations across all PG branches under unified management. 

### Core Distinction: Not an E-Commerce / Listing App
This application is **NOT** a public hostel search app (e.g., Airbnb, MagicBricks, or Zolo). Students do not browse or search through hundreds of listings from home.

### Actual Physical-First Business Workflow
```
Student visits PG branch physically
               │
               ▼
Sub Admin shows available rooms & beds
               │
               ▼
Student inspects rooms and agrees to stay
               │
               ▼
Student scans QR Code displayed at that specific PG branch
               │
               ▼
Flutter App launches & automatically identifies the branch via QR payload
               │
               ▼
App displays data strictly for that branch only
               │
               ▼
Student selects room and available bed
               │
               ▼
Student completes registration (personal details & KYC upload)
               │
               ▼
Booking request is submitted to the system
               │
               ▼
Sub Admin verifies payment proof and KYC details
               │
               ▼
Sub Admin approves booking
               │
               ▼
Student becomes an Active Resident
               │
               ▼
App transitions into a Daily Resident Companion App
```

---

## 2. Technical Architecture

The architecture enforces a single centralized backend structure to ensure data consistency, centralized reporting, and seamless scalability.

```
rudragrouppg/
├── admin/                     # Laravel Application
│   ├── Super Admin Portal     # Master control panel for all branches
│   ├── Sub Admin Portal       # Branch-level operational portal
│   ├── REST API Module        # JSON endpoints for mobile & web apps
│   └── Shared Storage         # KYC documents, payment receipts, meter photos
│
├── student/                   # Flutter Application
│   ├── Android                # Native Android build target
│   └── iOS                    # Native iOS build target
│
└── Shared Central Database    # Single MySQL relational database instance
```

### Architectural Guarantees:
1. **Single Centralized Database**: All branches, users, rooms, beds, bookings, payments, and logs reside in one database.
2. **Single Unified API**: Laravel serves all API requests for both web dashboards and the Flutter mobile application.
3. **Single File Storage**: Document uploads (Aadhaar, PAN, payment receipts, meter photos) are stored in shared, structured storage.
4. **Infinite Branch Scalability**: New branches can be onboarded dynamically by Super Admin without database migration or code changes.

---

## 3. User Hierarchy & Roles (RBAC)

The system defines three strict user roles:

| Role | Access Level | Managed System | Primary Responsibilities |
| :--- | :--- | :--- | :--- |
| **Super Admin** | Unlimited / Master | Laravel Web Admin | Full system oversight, branch creation, Sub Admin assignments, financial reporting, system config, global audit logs. |
| **Sub Admin** | Branch-Scoped | Laravel Web Admin | Operational management of assigned branches, bed allocation, booking approval, payment verification, electricity reading verification. |
| **Student** | Resident-Scoped | Flutter Mobile App | Bed selection, registration submission, rent & deposit tracking, electricity reading submission, complaint tracking, profile management. |

---

## 4. Module Breakdown by User Role

### A. Super Admin Modules (Laravel Web)
1. **Executive Dashboard**: Real-time revenue, overall occupancy rates, branch performance, pending booking requests, active complaints.
2. **Branch Management**: Create, update, deactivate PG branches; assign unique QR codes and branch managers.
3. **Sub Admin Management**: Create Sub Admin accounts and bind them to one or multiple PG branches.
4. **Room & Bed Master**: Configure rooms, bed counts, sharing types (1/2/3/4 sharing), rent tariffs, security deposit rates, and amenities.
5. **Student Directory**: Master database of all registered and resident students across all branches.
6. **Booking Management**: Override approvals, view booking histories, handle cancellations and room transfers.
7. **Financial & Payment Hub**: Consolidated revenue reports, deposit holding summaries, outstanding rent ledgers.
8. **Electricity Master**: Set per-unit electricity rates per branch, audit billing submissions.
9. **Reports & Analytics**: PDF/Excel exports for financial audits, occupancy metrics, payment defaults.
10. **System Settings & Audit Logs**: Global platform settings, security settings, immutable admin activity logs.

### B. Sub Admin Modules (Laravel Web)
1. **Branch Dashboard**: Daily occupancy metrics for assigned branches, pending approvals, overdue payments.
2. **Student Verification**: Review incoming booking requests, verify Aadhaar/PAN documents, verify payment screenshots.
3. **Room & Bed Allocation**: Assign or transfer beds visually, flag beds under maintenance or reserved.
4. **Monthly Rent Collection**: Track rent dues, record cash payments, verify UPI transactions.
5. **Deposit Verification**: Verify initial security deposit payments before room key handover.
6. **Electricity Bill Verification**: Audit student-submitted meter readings and photo proof, approve calculated amounts.
7. **Complaint & Notice Desk**: Respond to student complaints, issue branch notices and announcements.
8. **Branch Reports**: Daily collection summaries, occupancy status reports for local branch.

### C. Student Companion Modules (Flutter App)
1. **Onboarding & Branch Lock**: Splash screen, welcome flow, QR branch detection mechanism.
2. **Property Overview & Booking**: Branch details, room catalog, visual bed matrix, registration & KYC submission.
3. **Resident Portal (Post-Approval)**:
   - **My Room**: View room number, assigned bed, roommates, joining date, monthly rent rate.
   - **My Payments**: Outstanding rent, security deposit breakdown, payment proof uploader (Cash/UPI/Bank), payment receipts history.
   - **Electricity Submission**: Monthly meter reading form with camera snapshot attachment and bill history.
   - **Notifications & Announcements**: Notices from Sub Admin, rent reminders, approval alerts.
   - **Documents Vault**: View uploaded Aadhaar, PAN, and payment verification receipts.
   - **Support & Complaints**: One-tap phone/WhatsApp connection to branch manager, complaint ticketing system.

---

## 5. Key Operational Workflows

### A. Peer-to-Peer (P2P) Payment Workflow
The system does not integrate direct online payment gateways (e.g. Razorpay/Stripe) for transaction processing. Payments are offline P2P transfers:
1. System generates payment due notice (Monthly Rent, Deposit, or Electricity).
2. Student makes offline payment (Cash to Sub Admin OR Direct UPI / Bank Transfer via displayed QR/UPI ID).
3. Student uploads transaction proof (Reference Number + Payment Screenshot) via Flutter app.
4. Sub Admin receives verification task on Laravel dashboard.
5. Sub Admin verifies funds received in bank/cash register and clicks **Approve**.
6. System updates ledger balance to Paid and generates a digital receipt.

### B. Monthly Electricity Billing Workflow
1. On the 1st of every month, student receives an electricity reading submission prompt.
2. Student enters current meter reading number and attaches a live photograph of the physical meter.
3. Sub Admin audits the reading against previous month's final reading and photo proof.
4. Upon Sub Admin approval, system auto-calculates total units consumed \(\times\) branch unit rate.
5. Electricity bill is added to the student's monthly payment dues.

---

## 6. Future Scalability Roadmap

The architecture is prepared for seamless integration of advanced features without schema restructuring:
- **Biometric & Smart Door Locks**: API readiness for IoT access control based on active resident status.
- **Visitor & Leave Management**: Resident gate-pass requests and visitor log tracking.
- **Food & Laundry Management**: Daily meal opt-in/opt-out and laundry token tracking.
- **WhatsApp & Push Notifications**: Automated rent reminder triggers via WhatsApp Business API and FCM.
