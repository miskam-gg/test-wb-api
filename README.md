**ОПИСАНИЕ**
**Я НЕ НАШЕЛ БЕСПЛАТНЫЕ ХОСТЫ БД, В ФАЙЛЕ [example.txt](example.txt) ПРИВЕЛ ПРИМЕР ВЫВОДА В БД**

Тестовое Laravel-приложение, которое загружает данные по четырём сущностям (продажи, заказы, склады, доходы) из внешнего API и сохраняет их в MySQL базу данных.


*Установка и запуск:*
"git clone https://github.com/miskam-gg/test-wb-api"

*Создание и настройка переменных окружения:*
"cp .env.example .env"

*Параметры подключения к БД:*
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=max
DB_PASSWORD=nosecret   <======== в docker-compose.yml данные доступа

*Эндпоинты и Ключ:*
EXTERNAL_API_KEY=E6kUTYrYwZq2tN4QEtyzsbEBk3ie
EXTERNAL_API_ORDERS_URL=http://109.73.206.144:6969/api/orders
EXTERNAL_API_SALES_URL=http://109.73.206.144:6969/api/sales
EXTERNAL_API_STOCKS_URL=http://109.73.206.144:6969/api/stocks
EXTERNAL_API_INCOMES_URL=http://109.73.206.144:6969/api/incomes

*Установка зависимостей:*
"docker-compose run --rm app composer install"

*Запуск контейнеров:*
"docker-compose up -d"

*Миграции:*
"docker-compose exec app php artisan migrate"


**Загрузка данных из API:**

После запуска проекта, используются команды, для загрузки данных:

*Заказы:*
"docker-compose exec app php artisan fetch:orders 2025-03-01 2025-03-31 --page=1 --limit=500"

*Продажи:*
"docker-compose exec app php artisan fetch:sales 2025-03-01 2025-03-31 --page=1 --limit=500"

*Склады:*
"docker-compose exec app php artisan fetch:stocks 2025-03-24 --page=1 --limit=500"

*Доходы:*
"docker-compose exec app php artisan fetch:incomes 2025-03-01 2025-03-31 --page=1 --limit=500"
