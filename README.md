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

#### Live Demo

You can check the [live demo here](https://gdstore.bmehul.com)

#### Notes

Optionally, increase the following sizes in your php.ini config file

```bash
sudo gedit /etc/php/8.2/apache2/php.ini

max_file_uploads = 500
post_max_size = 2G
upload_max_filesize = 2G
```

If you are using Laravel Sail, refer to their [official documentation](https://laravel.com/docs/10.x/sail) to make the necessary changes.

#### License

This project is an open-sourced software licensed under the [MIT License](https://opensource.org/license/mit)
