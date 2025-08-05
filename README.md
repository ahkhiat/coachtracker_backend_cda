# ⚽ CoachTracker - Backend API

This repository contains the **backend** of the CoachTracker application, a mobile and web solution designed to manage a football club.

## 📱 Purpose

CoachTracker enables **coaches**, **players**, **parents**, and **club staff** to efficiently manage:

- Match and training **invitations**
- Player **attendance tracking**
- **Statistics** monitoring (goals, absences, participation, etc.)

---

## ⚙️ Technology Stack

- **Framework:** Symfony 6.4
- **API:** API Platform  
- **Database:** MySQL (currently local)  
- **Future deployment:** Oracle server

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.x  
- Composer  
- MySQL  
- Symfony CLI (optional, but recommended)

### Installation

1. Clone the repository  
   ```bash
   git clone https://github.com/yourusername/coachtracker_backend.git
   cd coachtracker_backend
   ```

2. Install dependencies

    ```bash
    composer install
    Configure your .env file for your database connection (MySQL local for now).
    ```

3. Create the database and run migrations

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

    Run the Symfony server
    ```bash
    symfony server:start
    ```

    Access the API documentation at:
    ```bash
    http://localhost:8000/api/docs
    ````

## 🛠️ Features
- User authentication with JWT

- CRUD for players, coaches, matches, convocations

- Attendance and statistics tracking

- Swagger/OpenAPI documentation for all API endpoints

## 📦 Deployment
Currently deployed locally with MySQL. Future plans include deploying on an Oracle server for production use.

## 🤝 Contributing
Contributions are welcome! Please open issues or pull requests.

## 📄 License
[Specify your license here]

If you need any help, feel free to contact me!

---
