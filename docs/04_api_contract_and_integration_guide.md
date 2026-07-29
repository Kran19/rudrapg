# Document 04: API Contract & Integration Guide

## 1. Overview & Protocol Standard

This document defines the REST API contract between the **Laravel Backend** and the **Flutter Student Application**.

- **Base URL**: `https://api.rudragrouppg.com/api/v1`
- **Protocol**: HTTPS / REST
- **Authentication**: Laravel Sanctum Bearer Tokens
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <SANCTUM_TOKEN>
  ```

---

## 2. Standard Response Envelope

All API endpoints return JSON structured in a uniform envelope:

### Success Response Envelope:
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {}
}
```

### Error Response Envelope:
```json
{
  "success": false,
  "message": "Validation failed or request error.",
  "errors": {
    "field_name": [
      "Error detail description."
    ]
  }
}
```

---

## 3. Endpoints & Payload Specifications

### A. Authentication Endpoints

#### `POST /auth/phone-login`
- **Purpose**: Authenticate student via phone number.
- **Request Body**:
  ```json
  {
    "phone": "9876543210"
  }
  ```
- **Response Data**:
  ```json
  {
    "success": true,
    "message": "Authentication successful.",
    "data": {
      "user_id": 42,
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "token": "1|sanctum_token_string_here",
      "is_resident": true,
      "resident_status": "active"
    }
  }
  ```

---

### B. Branch & Room Endpoints

#### `GET /branches/qr/{qr_hash}`
- **Purpose**: Resolve scanned QR code hash to branch details.
- **Response Data**:
  ```json
  {
    "success": true,
    "message": "Branch identified.",
    "data": {
      "branch_id": 1,
      "branch_code": "PG-NRD-01",
      "name": "Naroda Branch",
      "address": "102, Main Highway Road, Naroda",
      "contact_number": "+91 98765 12345",
      "manager_name": "Suresh Patel",
      "electricity_unit_rate": 10.00
    }
  }
  ```

#### `GET /branches/{branch_id}/rooms`
- **Purpose**: Fetch available rooms inside the locked branch.
- **Response Data**:
  ```json
  {
    "success": true,
    "data": [
      {
        "room_id": 101,
        "room_number": "101",
        "floor": 1,
        "sharing_type": "2_sharing",
        "monthly_rent": 6500.00,
        "security_deposit": 10000.00,
        "available_beds": 1,
        "total_beds": 2,
        "is_ac": true,
        "has_attached_washroom": true
      }
    ]
  }
  ```

#### `GET /rooms/{room_id}/beds`
- **Purpose**: Fetch visual bed layout matrix for a selected room.
- **Response Data**:
  ```json
  {
    "success": true,
    "data": {
      "room_id": 101,
      "room_number": "101",
      "beds": [
        {
          "bed_id": 501,
          "bed_code": "Bed-101-A",
          "status": "occupied"
        },
        {
          "bed_id": 502,
          "bed_code": "Bed-101-B",
          "status": "available"
        }
      ]
    }
  }
  ```

---

### C. Booking & Registration Endpoints

#### `POST /bookings`
- **Purpose**: Submit bed booking request and upload KYC files (`multipart/form-data`).
- **Form Data Fields**:
  - `branch_id`: `1`
  - `room_id`: `101`
  - `bed_id`: `502`
  - `full_name`: `Rahul Sharma`
  - `phone`: `9876543210`
  - `aadhaar_number`: `123456789012`
  - `pan_number`: `ABCDE1234F`
  - `aadhaar_front`: `<BINARY_FILE>`
  - `aadhaar_back`: `<BINARY_FILE>`
  - `pan_card`: `<BINARY_FILE>`
- **Response Data**:
  ```json
  {
    "success": true,
    "message": "Booking request submitted successfully.",
    "data": {
      "booking_id": 89,
      "booking_number": "BK-2026-0089",
      "status": "pending",
      "submitted_at": "2026-07-29 16:30:00"
    }
  }
  ```

---

### D. Resident Portal & Payment Endpoints

#### `GET /student/dashboard`
- **Purpose**: Retrieve active resident dashboard data post-approval.
- **Response Data**:
  ```json
  {
    "success": true,
    "data": {
      "resident_info": {
        "resident_id": 12,
        "branch_name": "Naroda Branch",
        "room_number": "101",
        "bed_code": "Bed-101-B",
        "joining_date": "2026-08-01"
      },
      "dues_summary": {
        "monthly_rent": 6500.00,
        "rent_due_date": "2026-08-05",
        "rent_status": "unpaid",
        "electricity_due": 450.00,
        "security_deposit_paid": 10000.00
      }
    }
  }
  ```

#### `POST /payments/submit-proof`
- **Purpose**: Upload P2P payment screenshot proof (`multipart/form-data`).
- **Form Data Fields**:
  - `resident_id`: `12`
  - `payment_type`: `rent` // rent, security_deposit, electricity
  - `amount`: `6500.00`
  - `payment_mode`: `upi` // cash, upi, bank_transfer
  - `transaction_reference`: `UPI/123499887766`
  - `proof_screenshot`: `<BINARY_FILE>`
- **Response Data**:
  ```json
  {
    "success": true,
    "message": "Payment proof submitted for Sub Admin verification.",
    "data": {
      "transaction_id": "PAY-2026-9912",
      "status": "pending"
    }
  }
  ```

#### `POST /electricity/submit-reading`
- **Purpose**: Submit monthly meter reading and camera photograph (`multipart/form-data`).
- **Form Data Fields**:
  - `resident_id`: `12`
  - `room_id`: `101`
  - `meter_reading_value`: `14520.50`
  - `meter_photo`: `<BINARY_FILE>`
- **Response Data**:
  ```json
  {
    "success": true,
    "message": "Meter reading submitted successfully.",
    "data": {
      "reading_id": 45,
      "status": "submitted"
    }
  }
  ```
