## GD Clone

This is a simple google drive clone built using Laravel and VueJs via InteriaJs.

### Installation

Run the following command one-by-one

```bash
git clone git@github.com:MehulBawadia/google-drive-clone.git
cd google-drive-clone
cp .env.example .env ## Don't forget to update the DB_* credentials in the .env file
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve --host=localhost
npm run dev
```

#### License

This project is an open-sourced software licensed under the [MIT License](https://opensource.org/license/mit)
