# Project Installation Guide

This guide will help you set up the project environment from cloning the repository to running it locally or on your server.

---

## 1. Clone the Repository

Use your GitHub credentials (username and token) to clone the project:

git clone https://<USERNAME>:<TOKEN>@github.com/<OWNER>/<REPO>.git
cd <REPO>

---

## 2. (Optional) Configure Git Remote

If you need to set or update the Git remote manually:

git remote add origin https://<your_token>@github.com/username/repository.git



---

## 3. Reset and Update Codebase (Optional, for clean state)

git reset --hard
git clean -fd
git checkout main
git pull origin main

---

## 4. Install PHP Dependencies

composer install


---

## 5. Set Up Environment Variables

Copy `.env.example` to `.env` if `.env` is not already present:


Edit `.env` to configure your database credentials, app URL, and other environment-specific settings.

---

## 6. Generate Application Key

php artisan key:generate


---

## 7. Set Up Database

Run migrations and optionally seed the database:

php artisan migrate --seed


Or if migrations are done and you only want to seed:

php artisan db:seed


---

## 8. Install Node Version Manager (NVM), Node.js v18 and npm

If Node.js is not installed, install nvm first:

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.4/install.sh | bash
source ~/.bashrc


Use nvm to install and use Node.js version 18:

nvm install 18
nvm use 18

Verify installations:

node -v
npm -v


---

## 9. Install Node.js Dependencies

npm install


---

## 10. Build Frontend Assets

For production build:

npm run build


For development with hot-reloading:

npm run dev


---

## 11. Server Configuration (Apache/Nginx)

- Make sure the `.htaccess` file is present in the `public` directory.
- Set the document root of your web server to the `public` folder.

---

## 12. Access the Application

Open your browser and visit the configured app URL.

---

## Summary of Commands

git clone https://<USERNAME>:<TOKEN>@github.com/<OWNER>/<REPO>.git
cd <REPO>

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.4/install.sh | bash
source ~/.bashrc
nvm install 18
nvm use 18

npm install
npm run build

---

# Notes

- Replace placeholder values (`<USERNAME>`, `<TOKEN>`, `<OWNER>`, `<REPO>`) with your actual data.
- Ensure your database is running and accessible when running migrations.
- For any issues with permissions, verify user access to folders and files.

---

This guide should help you get your Laravel + Node.js project running smoothly.
