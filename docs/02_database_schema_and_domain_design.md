# Document 02: Database Schema & Domain Design

## 1. Central Database Strategy

The **Rudra Group PG** system relies on a single MySQL database powering all PG branches. Branch isolation is achieved logically via `branch_id` scoping across tables.

```
                    ┌─────────────────┐
                    │    branches     │
                    └────────┬────────┘
                             │ 1
                             │
            ┌────────────────┼────────────────┬────────────────┐
            │ *              │ *              │ *              │ *
    ┌───────┴──────┐ ┌───────┴──────┐ ┌───────┴──────┐ ┌───────┴──────┐
    │ branch_user  │ │    rooms     │ │   bookings   │ │   residents  │
    └──────────────┘ └───────┬──────┘ └───────┬──────┘ └───────┬──────┘
                             │ 1              │ 1              │ 1
                             │                │                │
                             │ *              │ *              │ *
                     ┌───────┴──────┐ ┌───────┴──────┐ ┌───────┴──────┐
                     │     beds     │ │   payments   │ │ electricity  │
                     └──────────────┘ └──────────────┘ └──────────────┘
```

---

## 2. Table Definitions & Relational Schema

### A. Core System & Access Control

#### `branches`
Stores PG branch metadata and QR code identifiers.
```sql
CREATE TABLE branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_code VARCHAR(50) UNIQUE NOT NULL, -- e.g., 'PG-NRD-01'
    name VARCHAR(150) NOT NULL,              -- e.g., 'Naroda Branch'
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL DEFAULT 'Ahmedabad',
    state VARCHAR(100) NOT NULL DEFAULT 'Gujarat',
    pincode VARCHAR(10) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    manager_name VARCHAR(100) NULL,
    qr_code_hash VARCHAR(255) UNIQUE NOT NULL, -- Matched against QR scan
    electricity_unit_rate DECIMAL(8, 2) NOT NULL DEFAULT 10.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `users`
Unified table for Super Admins, Sub Admins, and Registered Students.
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NULL, -- Optional for quick student login
    role ENUM('super_admin', 'sub_admin', 'student') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    fcm_token VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `branch_user`
Pivot table establishing multi-branch assignment for Sub Admins.
```sql
CREATE TABLE branch_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY user_branch_unique (user_id, branch_id)
);
```

---

### B. Property Management Schema

#### `rooms`
Defines physical rooms inside each branch.
```sql
CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    room_number VARCHAR(50) NOT NULL, -- e.g., '101', '202-A'
    floor_number INT NOT NULL DEFAULT 1,
    sharing_type ENUM('1_sharing', '2_sharing', '3_sharing', '4_sharing') NOT NULL,
    monthly_rent DECIMAL(10, 2) NOT NULL,
    security_deposit DECIMAL(10, 2) NOT NULL,
    is_ac BOOLEAN DEFAULT FALSE,
    has_attached_washroom BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    status ENUM('available', 'full', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY branch_room_unique (branch_id, room_number)
);
```

#### `beds`
Defines individual beds within a room for visual interactive selection.
```sql
CREATE TABLE beds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    bed_code VARCHAR(50) NOT NULL, -- e.g., 'Bed-A', 'Bed-B'
    status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY room_bed_unique (room_id, bed_code)
);
```

---

### C. Booking & Resident Schema

#### `bookings`
Stores initial student booking applications before physical bed allocation approval.
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(50) UNIQUE NOT NULL, -- e.g., 'BK-2026-0089'
    student_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    bed_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    aadhaar_number VARCHAR(20) NOT NULL,
    pan_number VARCHAR(20) NULL,
    aadhaar_front_url VARCHAR(255) NOT NULL,
    aadhaar_back_url VARCHAR(255) NOT NULL,
    pan_card_url VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    rejection_reason TEXT NULL,
    processed_by BIGINT UNSIGNED NULL, -- Sub Admin user_id
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `residents`
Maintains official active resident records after booking approval.
```sql
CREATE TABLE residents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL UNIQUE,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    bed_id BIGINT UNSIGNED NOT NULL,
    joining_date DATE NOT NULL,
    vacating_date DATE NULL,
    status ENUM('active', 'vacated', 'evicted') DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE CASCADE
);
```

---

### D. Financial & Meter Schema

#### `payments`
Tracks Peer-to-Peer payment submissions and Sub Admin verification status.
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(100) UNIQUE NOT NULL, -- e.g., 'PAY-2026-9912'
    resident_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    payment_type ENUM('rent', 'security_deposit', 'electricity') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_mode ENUM('cash', 'upi', 'bank_transfer') NOT NULL,
    transaction_reference VARCHAR(100) NULL, -- UPI Ref / Bank UTR
    proof_screenshot_url VARCHAR(255) NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by BIGINT UNSIGNED NULL, -- Sub Admin ID
    verified_at TIMESTAMP NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `electricity_readings`
Stores monthly student meter submissions and verified billing calculations.
```sql
CREATE TABLE electricity_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    reading_date DATE NOT NULL,
    meter_reading_value DECIMAL(10, 2) NOT NULL,
    meter_photo_url VARCHAR(255) NOT NULL,
    units_consumed DECIMAL(10, 2) NULL,
    total_amount DECIMAL(10, 2) NULL,
    status ENUM('submitted', 'approved', 'rejected') DEFAULT 'submitted',
    approved_by BIGINT UNSIGNED NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

### E. Support & System Schema

#### `complaints`
```sql
CREATE TABLE complaints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    resident_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    category ENUM('plumbing', 'electrical', 'cleaning', 'wifi', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    resolution_notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);
```

#### `audit_logs`
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(150) NOT NULL,
    module VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 3. Database Indexing & Performance Strategy

To maintain sub-second queries as data grows across multiple branches:
- **`branch_id` Indexing**: Composite indexes on `(branch_id, status)` across `rooms`, `bookings`, `residents`, `payments`.
- **Bed Lookup Optimization**: Unique index on `(room_id, bed_code)` ensuring instant visual bed grid rendering.
- **Fast QR Resolution**: Index on `qr_code_hash` in `branches`.
