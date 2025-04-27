# Voxa Forum

**Voxa** is a modern, lightweight forum software built with PHP, Symfony, and MariaDB.  
It was designed primarily as a **demo** and **portfolio project**, showcasing best practices in backend and frontend development for forums.

> ⚡ **Note:** This software is not production-ready. It is intended for demonstration and testing purposes only. Usage is entirely at your own risk.

---

## ✨ Features

- User registration with email verification
- Role-based permissions
- Forum categories, forums, topics, and posts
- BBCode parsing and safe HTML output
- Search functionality
- Admin Control Panel (ACP) for managing users, forums, and permissions
- Multi-language support

---

## 🛠️ Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ChristianSeip/voxa-forum.git
   cd voxa-forum
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Create and Configure `.env.local`**

   Copy the example file:
   ```bash
   cp .env .env.local
   ```

   Then adjust the following values inside `.env.local`:
    - `DATABASE_URL` → Your MariaDB/MySQL connection
    - `MAILER_DSN` → Mail configuration (optional for email features)
    - `APP_ENV=prod`
    - `APP_SECRET` → **Generate a new secret**:
      ```bash
      php bin/console secrets:generate-keys
      php bin/console secrets:set APP_SECRET
      ```

4. **Database Setup**

   Run the built-in installer:
   ```bash
   php bin/console app:install
   ```

   This will:
    - Run all migrations
    - Create default roles
    - Create an admin user (you'll be prompted for username, email, and password)
    - Create default forums

5. **Compile Frontend Assets**

   If your project uses frontend assets (optional):
   ```bash
   npm run build
   ```

6. **Start the Development Server**
   ```bash
   symfony server:start
   ```

---

## 📜 License

This project is licensed under a custom license.  
You may use and modify the software for **private** and **commercial** purposes, but **redistribution is strictly prohibited**.

> Please see the full [LICENSE](LICENSE.txt) file for detailed terms and conditions.

---

## 👨‍💻 Author

**Christian Seip**  
[https://www.seip.io](https://www.seip.io)