# Google PubSub Management UI

This project provides a simple user interface to manage Google Pub/Sub topics, subscriptions, and messages. It can interact with both Google Cloud Pub/Sub and the Pub/Sub emulator hosted in the local environment. The application is built on Laravel and utilizes Laravel Filament. You can modify the application as per your requirements.

## Setting Up the Application

To run the application, ensure that you have PHP installed along with the PHP package manager Composer. Also, you need to have the database of your choice installed. Once everything is set and you have cloned the codebase, follow these steps:

1. Run `composer install` command to install all the dependencies.
2. Run `php artisan migrate --seed` to run migrations and create the user account.

## Running the Application

You can run the application in different ways:

- If you have Valet installed, you can simply link the application using `valet link` command.
- Alternatively, you can run `php artisan serve` command to start your local server.

For more information about setting up and running a Laravel application, visit [Laravel Documentation](https://laravel.com/docs/10.x/installation).

## Setting Up Google Pub/Sub Emulator

Google Pub/Sub emulator provides a simple and easy environment for developing and testing applications that interact with Google Pub/Sub. Follow these steps to set it up:

1. Visit [Google Pub/Sub Emulator Documentation](https://cloud.google.com/pubsub/docs/emulator) to get more information on how to install the Google Pub/Sub emulator.
2. Once the emulator is in place, run `gcloud beta emulators pubsub start --project=PROJECT_ID` command to start your Pub/Sub emulator for the project PROJECT_ID. The default Pub/Sub URL would be `localhost:8085`, you can specify this if needed.

To interact with the Pub/Sub emulator, you need to have a service key. Follow these steps:

1. Generate the service key by running `gcloud auth application-default login` command.

## Connecting the Pub/Sub with the Application

Once the application is set up and the Google Pub/Sub is running locally:

1. Log in to the application using the default credentials:
    - Username: `admin@example.com`
    - Password: `password`

2. After login, create a project with the same PROJECT_ID used to start the Pub/Sub emulator.
3. You can now interact with the Topics, Subscriptions, and messages via the application.
4. If you need to have a default configuration for your Pub/Sub
   - Set the `GOOGLE_CLOUD_KEY_FILE` in your `.env` file to the path of the generated key file.
   - Set the `PUBSUB_EMULATOR_HOST` in your `.env` file to your local emulator path.


## Useful Links

- [Laravel](https://laravel.com/)
- [Laravel Filament](https://filamentphp.com/)
- [Google PubSub](https://cloud.google.com/pubsub/)
