@servers(['web' => 'localhost'])

@task('update')
    composer install
    php artisan migrate
    php artisan db:seed --class=NotificationsTableSeeder
@endtask
