# Rudra Group PG Management System - Documentation Index

Welcome to the central technical documentation for the **Rudra Group PG Management System**.

## System Architecture Overview

The system operates as a unified platform supporting multi-branch Paying Guest (PG) facilities. It consists of a single Laravel backend with a single centralized MySQL database, serving a web dashboard for Super/Sub Admins and a Flutter mobile app for resident students.

```
rudragrouppg/
│
├── docs/                                  # Technical & Architectural Documentation
│   ├── README.md                          # Documentation Index (This file)
│   ├── 01_master_architecture_and_business_model.md  # Core Business Flow, Roles & Modules
│   ├── 02_database_schema_and_domain_design.md       # Central DB Schema & Relationships
│   ├── 03_flutter_student_app_specification.md      # Flutter App UI/UX & Module Specs
│   └── 04_api_contract_and_integration_guide.md      # REST API Specification (Laravel <-> Flutter)
│
├── admin/                                 # Laravel Backend & Web Dashboard (Super Admin & Sub Admin)
│
└── student/                               # Flutter Mobile Application (Resident Companion App)
```

---

## Documentation Roadmap

1. **[Master Architecture & Business Model](file:///c:/Users/Admin/Desktop/projects/APPLICATIONS/rudraboyspg/docs/01_master_architecture_and_business_model.md)**
   - Physical visit & QR-code branch detection workflow.
   - User roles & permission boundaries (Super Admin, Sub Admin, Student).
   - Module breakdowns for Super Admin, Sub Admin, and Student.
   - Peer-to-Peer payment verification & Electricity workflow.

2. **[Database Schema & Domain Design](file:///c:/Users/Admin/Desktop/projects/APPLICATIONS/rudraboyspg/docs/02_database_schema_and_domain_design.md)**
   - Entity-Relationship definitions and foreign key constraints.
   - Branch isolation strategy (`branch_id` scoping).
   - Tables: `branches`, `users`, `branch_user`, `rooms`, `beds`, `bookings`, `residents`, `payments`, `electricity_readings`, `complaints`, `notices`, `audit_logs`.

3. **[Flutter Student App Specification](file:///c:/Users/Admin/Desktop/projects/APPLICATIONS/rudraboyspg/docs/03_flutter_student_app_specification.md)**
   - Design system: Brand colors, Poppins typography, Material Design 3, component styling.
   - Interactive Bed Selection visual layout rules.
   - Complete specifications for all 18 Student screens.
   - Code structure & state management integration architecture.

4. **[API Contract & Integration Guide](file:///c:/Users/Admin/Desktop/projects/APPLICATIONS/rudraboyspg/docs/04_api_contract_and_integration_guide.md)**
   - REST API specifications with HTTP status codes and Sanctum Bearer token authentication.
   - Endpoint payload schemas for QR code branch resolution, room availability, bed selection, registration, booking requests, rent payment proof, and electricity readings.

---

## Core Business Principles

- **No Public Browsing / Search**: Students do not browse PGs like Airbnb. The app is launched via QR code at the physical PG location.
- **Strict Branch Scoping**: The app renders data exclusively for the scanned branch until booking approval.
- **Peer-to-Peer Payments**: Cash or direct UPI/Bank transfer. Offline proof upload with Sub Admin manual verification.
- **Single Source of Truth**: One backend, one database, one storage API for all branches.
