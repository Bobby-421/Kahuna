# Kahuna Product Management System

## Features

- User Authentication System
  - Login/Signup functionality
  - Role-based access (Admin/User)
  - Token-based authentication

- Product Management
  - View product list
  - Add new products (Admin only)
  - Product details include:
    - Serial number
    - Product name
    - Warranty length

- Security Features
  - Password protection
  - Access level control
  - Token expiration (1 hour)
  - CORS protection


## Project Structure

```
├── api/
│   └── com/icemalta/kahuna/
│       ├── api/
│       │   └── Kahuna.php         # Main API handler
│       ├── model/
│       │   ├── AccessToken.php    # Token management
│       │   ├── DBConnect.php      # Database connection
│       │   ├── Product.php        # Product operations
│       │   └── User.php           # User operations
│       └── util/
│           └── ApiUtil.php        # API utilities
├── client/
│   ├── JS/
│   │   ├── index.js              # Login handling
│   │   ├── products.js           # Product management
│   │   └── signup.js             # User registration
│   ├── index.html                # Login page
│   ├── products.html             # Product management page
│   └── signup.html               # Registration page
└── support/
    └── db.sql                    # Database schema
```

## Setup Instructions

1. Database Setup:
   ```sql
   mysql -u root -p < support/db.sql
   ```

2. Running the Project:
   - Open a terminal in the project root directory
   - Execute the run script:
     ./run.cmd

   - This will:
     - Start the Docker containers for the application
     - Set up the PHP development server
     - Configure the database connection
     - Make the application available at http://localhost:8000

3. Accessing the Application:
   - Open your browser and navigate to http://localhost:8000
   - You'll be presented with the login page
   - Create a new account or use existing credentials to log in as 'user'

4. Default Admin Account:
   - Username: admin
   - Password: Admin
   - Use these credentials to access admin features

## API Endpoints

- `POST /api/user`
  - Create new user account
  - Required fields: username, email, password

- `POST /api/login`
  - Authenticate user
  - Required fields: username, password
  - Returns: token, user info, access level

- `GET /api/product`
  - Get list of products
  - Requires: Authentication token

- `POST /api/product`
  - Add new product (Admin only)
  - Required fields: serial, name, warrantyLength
  - Requires: Authentication token

## User Roles

1. Admin
   - Full access to all features
   - Can add new products
   - Can view all products

2. User
   - Can view products
   - Cannot add new products

## Postman Collection location
- "sc-bed-finalproject-RobertFenechScerri\support\Kahuna API.postman_collection.json"

## ERD Location
- "sc-bed-finalproject-RobertFenechScerri\support\ERD.png"
 




