News Portal

# Symfony News Portal

This is a demo News Portal project built with Symfony.  
It supports news articles, categories, comments, and an admin panel.

## Features

- Manage news articles and categories
- Add and delete comments on news
- Admin and public interfaces
- Database seeding for development/demo

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/zuriko/zurab_natroshvili_task.git

# Symfony News Portal – Quick Installation Guide

## Installation

### 1. Install Composer dependencies

- **Windows**
    ```bat
    composer install
    ```
- **Linux/Mac**
    ```bash
    composer install
    ```

---

### 2. Setup Database

- **Windows**
    ```bat
    php bin\console doctrine:database:create
    php bin\console doctrine:migrations:migrate
    ```
- **Linux/Mac**
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

---

### 3. Compile Frontend Assets

- **Windows**
    ```bat
    php bin\console asset-map:compile
    ```
- **Linux/Mac**
    ```bash
    php bin/console asset-map:compile
    ```

---

### 4. Start Symfony Local Server

- **Windows**
    ```bat
    symfony server:start
    ```
- **Linux/Mac**
    ```bash
    symfony server:start
    ```

---

### 5. Seed Demo Data

- **Windows**
    ```bat
    php bin\console app:seed-database
    ```
- **Linux/Mac**
    ```bash
    php bin/console app:seed-database
    ```

---

## Running Unit Tests

### 1. Create Test Database

- **Windows**
    ```bat
    php bin\console doctrine:database:create --env=test
    ```
- **Linux/Mac**
    ```bash
    php bin/console doctrine:database:create --env=test
    ```

### 2. Run Test Migrations

- **Windows**
    ```bat
    php bin\console doctrine:migrations:migrate --env=test --no-interaction
    ```
- **Linux/Mac**
    ```bash
    php bin/console doctrine:migrations:migrate --env=test --no-interaction
    ```

### 3. Run PHPUnit

- **Windows**
    ```bat
    php bin\phpunit
    ```
- **Linux/Mac**
    ```bash
    php bin/phpunit
    ```


## Weekly News Report Command
### Set up a Cron Job

To **automatically send the weekly news report every Monday at 08:00**:

```cron
0 8 * * 1 /path/to/php /path/to/project/bin/console app:weekly-news-report

# Run Manually (Locally)
    php bin/console app:weekly-news-report



Project Structure and Configuration
Entity Mappings (YAML):

    config/doctrine/Category.orm.yml
    config/doctrine/News.orm.yml
    config/doctrine/Comment.orm.yml

Admin Roles & Restrictions:
    config/packages/security.yaml

Main Pages:
    Public Home: http://127.0.0.1:8000/
    Admin Dashboard: http://127.0.0.1:8000/admin/dashboard


Controllers: src/Controller/
Templates: templates/
Tests: \tests\Integration
WeeklyNewsRepost:  Command\WeeklyNewsReportCommand.php
Validators: validator\
