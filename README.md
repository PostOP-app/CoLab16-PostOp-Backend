# PostCare Backend

This is the backend repository for the [PostCare app](https://postcare.pages.dev/), built using Laravel, a PHP framework, MySQL database and [StreamChat API](https://getstream.io) (for realtime messaging).

## Features

-   User authentication: Patients and Medical Providers registration
-   Creating and assigning of tasks to patients by registered Medical providers
-   Realtime messaging between patients and providers
-   CRUD operations on Recovery plans

## Requirements

-   PHP >= 8.0
-   MySQL >= 8.0
-   Composer
-   Laravel >= 9.0
-   Postman
-   A [Stream](https://getstream.io) account

## Installation

1. Clone this repository to your local machine using the following command:

`git clone https://github.com/PostOP-app/postcare-backend.git`

2. Navigate to the project directory:

`cd postcare-backend`

3. Install dependencies using Composer:

`composer install`

4. Copy the .env.example file to a new file named .env:
   `cp .env.example .env`

5. Generate an encryption key for Laravel:
   `php artisan key:generate`

6. Update the .env file with your database credentials and other configuration options.
7. Run the database migrations:

`php artisan migrate`

8. Run the following command to install [Laravel Passport](https://laravel.com/docs/9.x/passport#main-content) keys:

`php artisan passport:install`

## Usage

1. Start the built-in PHP server:

`php artisan serve`

2. Open your Postman app. The API endpoints can be accessed from here.

## API Endpoints

Run the following command in the project root directory to reveal the API endpoints:

`php artisan route:list`

## Contributing

1. Fork this repository
2. Create a new branch for your changes (e.g. feature/new-feature)
3. Commit your changes
4. Push to your fork and submit a pull request

## License

This project is licensed under the MIT License. See the [LICENSE](https://postcare.pages.dev) file for details.
