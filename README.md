# demo_laravel_store
## Demo utilizada durante FLISOL 2018 UGB

### Pasos de instalación
```
composer install
php artisan vendor:publish --provider="Konekt\Concord\ConcordServiceProvider" --tag=config
php artisan vendor:publish --tag=lang
mv .env.example .env
php artisan key:generate
```
Configurar base de datos y datos de paypal en archivo .env
```
php artisan migrate --seed
php artisan appshell:super
yarn install
yarn run dev
```
[Instalar yarn](https://yarnpkg.com/en/docs/install)
