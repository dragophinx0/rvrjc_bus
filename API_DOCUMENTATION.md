# RVR&JC Bus Tracking System - API Documentation

## 📖 System Overview

The **RVR&JC Bus Tracking System** is a platform designed to manage and track college buses in real-time. It serves four primary user roles:
1.  **Student**: Can view bus locations, check seat availability, reserve seats, and vote in daily polls.
2.  **Faculty**: Similar privileges to students but with faculty-specific verification.
3.  **Drivers**: Manage trips, update bus location, and view boarding passengers.
4.  **Coordinators (Admin)**: Manage buses, routes, timetables, and verify users.

### 🚀 Frontend Integration Guide

-   **Authentication**: Uses Laravel Sanctum (Bearer Token). All protected routes must include `Authorization: Bearer <token>` header.
-   **Base URL**: `/api`
-   **Date Format**: `YYYY-MM-DD`
-   **Time Format**: `HH:mm` (24-hour format)
-   **Location**: Latitude and Longitude are expected as `lat` and `lng` (decimal degrees).

---

## 🔐 Authentication

### 1. Register
**POST** `/auth/register`

Create a new user account. Users are `pending` until verified by OTP and then by a coordinator/faculty.

**Request Body:**
| Field | Type | Required | Description |
|---|---|---|---|
| `first_name` | String | Yes | User's first name |
| `last_name` | String | Yes | User's last name |
| `gender` | String | Yes | `male`, `female`, `other` |
| `email` | String | Yes | Must be a unique email |
| `password` | String | Yes | Min 8 chars, confirmed |
| `password_confirmation` | String | Yes | Same as password |
| `mobile_number` | String | Yes | Max 15 chars |
| `role` | String | Yes | `student`, `faculty`, `driver`, `bus_coordinator` |
| `roll_number` | String | Yes | **If role=student**. Unique ID |
| `course` | String | Yes | **If role=student**. `B.Tech`, `M.Tech`, etc. |
| `branch_id` | Integer | Yes | **If role=student/faculty**. ID from reference data |
| `year` | String | Yes | **If role=student**. e.g., "1", "2" |
| `date_of_birth` | Date | Yes | **If role=student**. `YYYY-MM-DD` |
| `employee_id` | String | Yes | **If role=faculty**. Unique ID |
| `designation_id` | Integer | Yes | **If role=faculty**. ID from reference data |

**Example Request:**
```json
{
  "first_name": "Rahul",
  "last_name": "Kumar",
  "gender": "male",
  "email": "rahul@rvrjc.ac.in",
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
  "user": { "id": 1, "name": "Rahul Kumar", "email": "rahul@rvrjc.ac.in", ... }
}
```

### 2. Verify OTP
**POST** `/auth/verify-otp`

Verify the email address using the OTP sent after registration.

**Request Body:**
```json
{
  "email": "rahul@rvrjc.ac.in",
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

Authenticate user and get access token. **User must be verified to login.**

**Request Body:**
```json
{
  "email": "rahul@rvrjc.ac.in",
  "password": "Password@123"
}
```

**Response (200 OK):**
```json
{
  "access_token": "1|AbCdeFGh...",
  "token_type": "Bearer",
  "user": { "id": 1, "role": "student", ... }
}
```

### 4. Logout
**POST** `/auth/logout`

Invalidate the current access token.
- **Auth Required**: Yes

**Response (200 OK):**
```json
{ "message": "Logged out successfully" }
```

### 5. Get Current User (Me)
**GET** `/auth/me`

Get details of the currently authenticated user.
- **Auth Required**: Yes

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "Rahul Kumar",
  "role": "student",
  "branch": { "id": 4, "name": "CSE" },
  "designation": null
}
```

---

## ✅ User Verification

### 1. Check My Verification Status
**GET** `/verification/status`

- **Auth Required**: Yes (Any user)

**Response:**
```json
{
  "is_verified": false,
  "verification_request": { "status": "pending", "updated_at": "..." }
}
```

### 2. Get Pending Verifications
**GET** `/verification/pending`

Get list of users waiting for verification.
- **Auth Required**: Admin (verifies Coordinators), Coordinator (verifies Drivers/Faculty), Faculty (verifies Students in their branch).

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "status": "pending",
      "user": { "name": "New Student", "branch": { "name": "CSE" } }
    }
  ]
}
```

### 3. Approve User
**POST** `/verification/approve/{user_id}`

- **Auth Required**: Authorized Verifier

**Response:**
```json
{ "message": "User verified successfully." }
```

### 4. Reject User
**POST** `/verification/reject/{user_id}`

- **Auth Required**: Authorized Verifier
- **Request Body**: `{ "reason": "Invalid ID proof" }`

**Response:**
```json
{ "message": "User verification rejected." }
```

---

## 🗳️ Polls (Daily Voting)

### 1. List Active Polls
**GET** `/polls`

Get available polls for voting.
- **Auth Required**: Yes

**Response:**
```json
[
  {
    "id": 10,
    "type": "morning",
    "date": "2026-02-12",
    "is_active": true,
    "votes": [] // Current user's vote if any
  }
]
```

### 2. Submit Vote
**POST** `/polls/{poll_id}/vote`

- **Auth Required**: Yes

**Request Body:**
```json
{
  "is_going": true,
  "stop_id": 5 // Required if is_going is true
}
```

**Response:**
```json
{ "message": "Vote recorded successfully", "vote": { ... } }
```

### 3. Poll Statistics
**GET** `/polls/stats`

View demand prediction stats.
- **Auth Required**: Admin, Bus Coordinator
- **Params**: `type` (morning/evening), `date` (YYYY-MM-DD)

**Response:**
```json
{
  "stats": {
    "stop_id_5": {
      "stop_name": "Main Gate",
      "total": 45,
      "students": 40,
      "faculty": 5
    }
  },
  "total_demand": 45
}
```

---

## 🚌 Bus Management

### 1. List Buses
**GET** `/buses`

- **Auth Required**: Yes
- **Params**: `bus_number`, `route_type`, `from_stop`, `to_stop`

**Response:**
```json
{
  "data": [
    { "id": 1, "bus_number": "AP 07 Z 1234", "routes": [...] }
  ]
}
```

### 2. Create Bus
**POST** `/buses`

- **Auth Required**: Admin, Coordinator

**Request Body:**
```json
{
  "bus_number": "AP 07 Z 9999",
  "capacity": 50
}
```

### 3. Get Bus Details
**GET** `/buses/{bus_id}`

Returns bus info, assigned routes, and current location.

**Response:**
```json
{
  "bus": { ... },
  "current_location": { "lat": 16.5, "lng": 80.5, "speed": 40 }
}
```

### 4. Update Bus
**PUT** `/buses/{bus_id}`
- **Auth Required**: Admin, Coordinator
- **Request Body**: `{ "bus_number": "...", "is_active": true }`

### 5. Delete Bus
**DELETE** `/buses/{bus_id}`
- **Auth Required**: Admin, Coordinator

### 6. Driver Select Bus
**POST** `/buses/{bus_id}/select`

Driver assigns themselves to a bus.
- **Auth Required**: Driver

**Response:**
```json
{ "message": "You have selected Bus #AP 07 Z 1234" }
```

### 7. Update Bus Location
**POST** `/buses/location`

- **Auth Required**: Driver
- **Request Body**:
```json
{
  "bus_id": 1,
  "lat": 16.5432,
  "lng": 80.6543,
  "speed": 45.5
}
```

---

## 🛣️ Trip Management

### 1. Start Boarding (Driver)
**POST** `/buses/{bus_id}/start-boarding`

Opens the bus for boarding (reservations).
- **Auth Required**: Driver

**Request Body:**
```json
{
  "route_id": 2,
  "shift": "first", // or 'second'
  "trip_type": "pickup", // or 'drop'
  "lat": 16.5,
  "lng": 80.5
}
```

**Response:**
```json
{ "message": "Boarding window opened", "trip": { "id": 101, ... } }
```

### 2. Start Journey
**POST** `/trips/{trip_id}/start-journey`

Locks seats and starts the trip.
- **Auth Required**: Driver
- **Response**: `{ "message": "Journey started..." }`

### 3. Complete Trip
**POST** `/trips/{trip_id}/complete`

Marks trip as finished.
- **Auth Required**: Driver

---

## 💺 Seats & Reservations

### 1. Generate Layout
**POST** `/buses/{bus_id}/generate-layout`
- **Auth Required**: Admin, Coordinator
- **Request Body**: None (Uses bus properties)

### 2. Get Bus Layout
**GET** `/buses/{bus_id}/layout`

See all seats and their status (available/reserved/occupied).
- **Response**:
```json
{
  "bus": { ... },
  "seats": [
    { "id": 1, "label": "1W", "type": "window", "reservations": [...] }
  ]
}
```

### 3. Reserve Seat
**POST** `/trips/{trip_id}/seats/{seat_id}/reserve`

Holds seat for 5 mins. **Must be within 50m of bus.**
- **Auth Required**: Student, Faculty
- **Request Body**: `{ "lat": 16.5, "lng": 80.5 }`

**Response:**
```json
{ "message": "Seat reserved for 5 minutes...", "reservation": { ... } }
```

### 4. Confirm Seat
**POST** `/reservations/{reservation_id}/confirm`

Permanently book seat. **Only allowed after journey starts.**
- **Auth Required**: Owner of reservation
- **Response**: `{ "message": "Seat confirmed!" }`

### 5. Extend Reservation
**POST** `/reservations/{reservation_id}/extend`

Add 5 mins to reservation timer. **Must be near bus.**
- **Request Body**: `{ "lat": 16.5, "lng": 80.5 }`

---

## 📍 Route Management

### 1. List Routes
**GET** `/routes`
- **Params**: `type` (pickup/drop)

### 2. Create Route
**POST** `/routes`
- **Auth Required**: Admin, Coordinator
- **Request Body**:
```json
{
  "name": "Route 5 - Guntur",
  "type": "pickup",
  "bus_id": 1,
  "stops": [
    { "name": "Lodge", "sequence": 1, "lat": 16.1, "lng": 80.1 },
    { "name": "Market", "sequence": 2, "lat": 16.2, "lng": 80.2 }
  ]
}
```

### 3. Get Route Details
**GET** `/routes/{route_id}`
- Returns route info with stops.

### 4. Delete Route
**DELETE** `/routes/{route_id}`

---

## 📅 Timetable Management

### 1. List Timetables
**GET** `/timetables`
- **Params**: `route_id`, `shift`

### 2. Create Timetable Entry
**POST** `/timetables`
- **Auth Required**: Admin, Coordinator
- **Request Body**:
```json
{
  "route_id": 1,
  "stop_id": 5,
  "shift": "first",
  "arrival_time": "08:15"
}
```

### 3. Update/Delete
- **PUT** `/timetables/{id}`
- **DELETE** `/timetables/{id}`

---

## 👥 User Management

### 1. List Users
**GET** `/users`
- **Auth Required**: Admin, Coordinator
- **Params**: `role`, `is_verified` (1/0)

### 2. Get User Details
**GET** `/users/{user_id}`

### 3. Update User
**PUT** `/users/{user_id}`

### 4. Delete User
**DELETE** `/users/{user_id}`
- **Auth Required**: Admin

### 5. Reference Data
**GET** `/reference-data`
- Returns plain lists of `branches` and `designations` for dropdowns.

---
