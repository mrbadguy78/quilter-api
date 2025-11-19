# Quilter Account/Transactions API technical test

## Objective
Build a secure Laravel API that manages financial transactions, with a strong focus
on authentication (Laravel Passport) and user authorization.

## Table of Contents

- [Setup Instructions](#setup-instructions)
- [Authentication](#authentication)
- [API Documentation](#api-documentation)
- [Error Handling](#error-handling)

---

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/mrbadguy78/quilter-api.git
cd quilter-api
```

### 2. Copy the .env file

```bash
cp .env.example .env
```

### 3. Start the Containers

```bash
docker-compose up -d
```

### 4. Run Migrations

```bash
docker-compose exec quilter-api php artisan migrate
```

### 5. Initialise Laravel Passport

```bash
docker-compose exec quilter-api php artisan passport:install
```

### 6. Run the Test Suite

```bash
docker-compose exec quilter-api php artisan test
```

---

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer <your-access-token>
```

Obtain a token by registering a new user via `POST /api/register`.

---

## API Documentation

**Base URL:** `http://localhost/api`

### Authentication Endpoints

#### Register a New User

Creates a new user account and returns an access token.

**Endpoint:** `POST /api/register`

**Authentication:** None required

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `email`: Required, valid email, unique
- `password`: Required, string, min 8 characters

**Success Response (201 Created):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhd..."
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### Account Endpoints

All account endpoints require authentication.

#### List User Accounts

Returns a paginated list of the authenticated user's accounts.

**Endpoint:** `GET /api/accounts`

**Authentication:** Required

**Query Parameters:**
- `page` (optional): Page number for pagination

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "name": "Savings Account",
            "balance": "1500.00",
            "created_at": "2025-11-18 12:00:00",
            "updated_at": "2025-11-18 12:00:00"
        }
    ],
    "links": {
        "first": "http://localhost/api/accounts?page=1",
        "last": "http://localhost/api/accounts?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 10,
        "to": 1,
        "total": 1
    }
}
```

#### Create an Account

Creates a new account for the authenticated user.

**Endpoint:** `POST /api/accounts`

**Authentication:** Required

**Request Body:**

```json
{
    "name": "Savings Account",
    "balance": "1000.00"
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `balance`: Optional, numeric, min 0 (defaults to 0)

**Success Response (201 Created):**

```json
{
    "id": 1,
    "user_id": 1,
    "name": "Savings Account",
    "balance": "1000.00",
    "created_at": "2025-11-18 12:00:00",
    "updated_at": "2025-11-18 12:00:00"
}
```

#### Get Account Details

Retrieves details of a specific account owned by the authenticated user.

**Endpoint:** `GET /api/accounts/{account}`

**Authentication:** Required

**URL Parameters:**
- `account`: Account ID

**Success Response (200 OK):**

```json
{
    "id": 1,
    "user_id": 1,
    "name": "Savings Account",
    "balance": "1500.00",
    "created_at": "2025-11-18 12:00:00",
    "updated_at": "2025-11-18 12:00:00"
}
```

**Error Response (403 Forbidden):**

```json
{
    "message": "This action is unauthorized."
}
```

---

### Transaction Endpoints

All transaction endpoints require authentication.

#### List Account Transactions

Returns a paginated list of transactions for a specific account.

**Endpoint:** `GET /api/accounts/{account}/transactions`

**Authentication:** Required

**URL Parameters:**
- `account`: Account ID

**Query Parameters:**
- `page` (optional): Page number for pagination

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "account_id": 1,
            "type": "deposit",
            "amount": "500.00",
            "new_balance": "1500.00",
            "created_at": "2025-11-18 12:00:00",
            "updated_at": "2025-11-18 12:00:00"
        }
    ],
    "links": {
        "first": "http://localhost/api/accounts/1/transactions?page=1",
        "last": "http://localhost/api/accounts/1/transactions?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 10,
        "to": 1,
        "total": 1
    }
}
```

#### Create a Transaction

Deposits or withdraws funds from an account.

**Endpoint:** `POST /api/accounts/{account}/transactions`

**Authentication:** Required

**URL Parameters:**
- `account`: Account ID

**Request Body:**

```json
{
    "type": "deposit",
    "amount": "500.00"
}
```

**Validation Rules:**
- `type`: Required, must be "deposit" or "withdrawal"
- `amount`: Required, numeric, min 0.01

**Success Response (201 Created):**

```json
{
    "id": 1,
    "account_id": 1,
    "type": "deposit",
    "amount": "500.00",
    "new_balance": "1500.00",
    "created_at": "2025-11-18 12:00:00",
    "updated_at": "2025-11-18 12:00:00"
}
```

**Error Response - Insufficient Funds (422 Unprocessable Entity):**

```json
{
    "message": "Insufficient funds for withdrawal."
}
```

---

## Error Handling

### Common Error Responses

**401 Unauthorized** - Missing or invalid authentication token:

```json
{
    "message": "Unauthenticated."
}
```

**403 Forbidden** - User lacks permission for the requested resource:

```json
{
    "message": "This action is unauthorized."
}
```

**404 Not Found** - Resource does not exist:

```json
{
    "message": "No query results for model [App\\Models\\Account]"
}
```

**422 Unprocessable Entity** - Validation errors or business rule violations:

```json
{
    "message": "The name field is required.",
    "errors": {
        "name": ["The name field is required."]
    }
}
```

---

## Possible improvements

- **Rate limiting** - Throttle API requests per user to prevent abuse (e.g., 60 requests/minute)
- **Pessimistic locking** - Use `lockForUpdate()` to prevent race conditions
- **Login endpoint** - Add `POST /api/login` to obtain tokens
- **Token revocation** - Add `POST /api/logout` to invalidate tokens
- **Refresh tokens** - Implement token refresh for better security
- **Filtering & sorting** - Allow filtering transactions by date, type, or amount
