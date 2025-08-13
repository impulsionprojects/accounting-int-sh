# Accounting 

## Table of Contents

- [Accounting](#Accounting )
    - [Introduction](#introduction)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
    - [Configuration](#configuration)
    - [Running the Application](#running-the-application)
    - [Running With Docker](#running-with-docker)
    - [Login](#login)
    - [Testing](#testing)

## Introduction

Accounting software

## Prerequisites

Ensure the following PHP extensions are installed on your system:
- `Minimum PHP version required: 8.1`
- `openssl`
- `pdo`
- `mbstring`
- `tokenizer`
- `JSON`
- `cURL`

Ensure the following folder permissions are set:
- `storage/` => `777`
- `storage/framework/` => `777`
- `storage/logs/` => `777`
- `storage/uploads/` => `777`
- `bootstrap/cache/` => `777`
- `resources/lang/` => `777`

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/abdullahhjiim/accountGo
    cd accountGo
    ```

2. Install PHP dependencies using Composer:
    ```sh
    composer install
    ```

3. Install JavaScript dependencies using npm:
    ```sh
    npm install
    ```

## Configuration

1. Copy the `.env.example` file to `.env`:
    ```sh
    cp .env.example .env
    ```

2. Generate the application key:
    ```sh
    php artisan key:generate
    ```

3. Configure your database and other environment variables in the `.env` file.

## Running the Application

1. Start the local development server:
    ```sh
    php artisan serve
    ```

2. Compile the assets:
    ```sh
    npm run dev
    ```

3. Access the application at `http://localhost:8000`.


## Running With Docker

1. Start the Docker containers:
    ```sh
    docker compose up -d
    ```

2. Access the application container:
    ```sh
    docker compose exec app sh
    ```

3. Generate the application key:
    ```sh
    php artisan key:generate
    ```
4. Access the application at `http://localhost:8181`.



## Login

Use the following credentials to log in:

- **Email:** `accountant@example.com`
- **Password:** `1234`



## Testing

Run the test suite:
```sh
php artisan test
