# RVR&JC Bus Tracking System - Complete API Documentation

## 🔐 Authentication

### 1. Register
**POST** `/auth/register`

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "gender": "male",
  "email": "l24co096@rvrjc.ac.in",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "mobile_number": "9876543210",
  "role": "student",
  "roll_number": "L24CO096",
  "course": "B.Tech",
  "branch_id": 4,
  "year": "1",
  "date_of_birth": "2005-05-15"
}
```

**Response (201 Created):**
```json
{
  "message": "Registration successful. Please verify your email with the OTP sent to your inbox.",
  "user": { ... }
}
```

### 2. Verify OTP
**POST** `/auth/verify-otp`

**Request Body:**
```json
{
  "email": "l24co096@rvrjc.ac.in",
  "otp": "123456"
}
```

**Response (200 OK):**
```json
{
  "message": "Email verified successfully. You can login once your account is verified by the coordinator/faculty."
}
```

### 3. Login
**POST** `/auth/login`

**Request Body:**
```json
{
  "email": "l24co096@rvrjc.ac.in",
  "password": "Password@123"
}
```

**Response (200 OK):**
```json
{
  "access_token": "1|AbCdeFGh...",
  "token_type": "Bearer",
  "user": { ... }
}
```

---

## ✅ Hierarchical Verification

### 1. Get Pending Verifications
**GET** `/verification/pending`
- **Auth required**: Coordinator/Faculty/Admin

**Response:**
```json
[
  {
    "id": 1,
    "user_id": 2,
    "status": "pending",
    "user": { ... }
  }
]
```

### 2. Approve User
**POST** `/verification/approve/{user_id}`

### 3. Reject User
**POST** `/verification/reject/{user_id}`
- **Request Body:** `{ "reason": "Documents not clear" }`

---

## 🗳️ Daily Voting (Polls)

### 1. List Active Polls
**GET** `/polls`

**Response:**
```json
[
  {
    "id": 1,
    "type": "morning",
    "date": "2026-02-11",
    "is_active": true,
    "votes": []
  }
]
```

### 2. Submit Vote
**POST** `/polls/{poll_id}/vote`

**Request Body:**
```json
{
  "is_going": true,
  "stop_id": 1
}
```

### 3. Demand Statistics (Coordinator)
**GET** `/polls/stats?type=morning&date=2026-02-11`

**Response:**
```json
{
  "stats": {
    "1": {
      "stop_name": "Main Gate",
      "total": 63,
      "males": 20,
      "females": 43,
      "students": 55,
      "faculty": 8
    }
  },
  "total_demand": 63
}
```

---

## 🚌 Bus & Trip Management

### 1. Generate Layout
**POST** `/buses/{bus_id}/generate-layout`
- **Auth required**: Coordinator/Admin

### 2. View Layout & Status
**GET** `/buses/{bus_id}/layout`

**Response:**
```json
{
  "bus": { ... },
  "seats": [
    {
      "id": 10,
      "label": "5W",
      "type": "window",
      "reservations": [
        {
          "status": "reserved",
          "user": { "gender": "female", "role": "student" }
        }
      ]
    }
  ]
}
```

### 3. Start Boarding (Driver)
**POST** `/buses/{bus_id}/start-boarding`

**Request Body:**
```json
{
  "route_id": 1,
  "shift": "first",
  "trip_type": "pickup",
  "lat": 16.25,
  "lng": 80.45
}
```

---

## 📍 Reservations

### 1. Reserve Seat
**POST** `/trips/{trip_id}/seats/{seat_id}/reserve`

**Request Body:**
```json
{
  "lat": 16.251,
  "lng": 80.451
}
```

### 2. Confirm Seat
**POST** `/reservations/{reservation_id}/confirm`

---

## 🗺️ Routes & Stops

### 1. List Routes
**GET** `/routes`

**Response:**
```json
[
  {
    "id": 1,
    "name": "Route 1 - Main City",
    "stops": [
      { "id": 1, "name": "Stop A", "latitude": 16.1, "longitude": 80.1 },
      { "id": 2, "name": "Stop B", "latitude": 16.2, "longitude": 80.2 }
    ]
  }
]
```

---

## 👤 User Management

### 1. My Profile
**GET** `/auth/me`

### 2. Reference Data
**GET** `/reference-data`
- Returns `branches` and `designations`.

---

## 🛠️ Automated Tasks
- `bus:cleanup-reservations`: (Every Minute) Deletes expired `reserved` seats.
- `poll:create-daily`: (Daily) Generates voting polls for the next pickup/drop.
