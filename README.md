# Cafe Booking API (Laravel + JWT)

Booking API для бронирования ресурсов (столиков в кафе) с авторизацией, ролями, бизнес-правилами бронирования, расписанием, поиском и отзывами.

## Быстрый запуск
1. Запуск проекта:
```bash
docker-compose up --build
```
2. Открыть Swagger UI: `http://localhost:8000/docs`
3. Получить токен администратора:
```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/auth/login" -ContentType "application/json" -Body '{"email":"admin@cafe.com","password":"password"}'
```
4. Прогнать тесты:
```bash
docker-compose exec app php artisan test
```

## Цель проекта
Собрать и защитить API для бронирования ресурсов с понятной моделью данных, строгими правилами доступа и проверяемой бизнес-логикой.

## Предметная область
Система бронирования столиков:
- пользователь регистрируется и логинится;
- пользователь ищет ресурсы, создаёт/отменяет свои бронирования;
- администратор управляет ресурсами и видит все бронирования;
- после завершённого бронирования пользователь оставляет отзыв;
- система считает средний рейтинг ресурса.

## Стек
- PHP 8.2
- Laravel 12
- JWT (`tymon/jwt-auth`)
- MySQL 8 (в Docker)
- PHPUnit (автотесты)
- OpenAPI 3 + Swagger UI
- Docker / Docker Compose

## Что сделано по чекпоинтам

### Чекпоинт 1. Проектирование и старт
- Выбрана предметная область: `Cafe Booking API`.
- Спроектирована модель данных и связи (`users`, `resources`, `bookings`, `reviews`).
- Сформирован API-контракт (реальные роуты в `src/routes/api.php`).
- Созданы и запускаются миграции.
- Подготовлена базовая структура проекта и README.

### Чекпоинт 2. Авторизация и CRUD ресурсов
- Реализованы регистрация/логин/логаут/me на JWT.
- Добавлена ролевая модель `admin/user`.
- CRUD ресурсов доступен только администратору.
- Добавлена валидация входных данных.
- Подготовлены seeders с тестовыми пользователями и данными.

### Чекпоинт 3. Бронирование
- Создание бронирования (дата, время начала/окончания).
- Ключевая бизнес-логика: проверка пересечений по времени.
- Отмена бронирования:
  - пользователь может отменить только своё;
  - администратор может отменить любое.
- Список бронирований:
  - пользователь видит свои;
  - администратор видит все.
- Логирование критичных бизнес-событий.

### Чекпоинт 4. Расписание, поиск, отзывы
- Расписание ресурса на день и на неделю.
- Поиск свободных ресурсов на дату/время.
- Фильтрация ресурсов по характеристикам (`type`, `capacity`, `max_price`, `location`).
- Пагинация и сортировка списков.
- Отзывы с оценкой и бизнес-ограничениями:
  - только владелец бронирования;
  - только после завершения бронирования;
  - запрет дублирующего отзыва.
- Средний рейтинг ресурса в ответах API.

### Чекпоинт 5. Тесты, Swagger, Docker
- Добавлены автотесты (8 тестов, критичные сценарии покрыты).
- Добавлена OpenAPI-спецификация и Swagger UI.
- Добавлены `Dockerfile` и `docker-compose.yml` (`app + db`).
- Проект запускается одной командой: `docker-compose up`.

## ER-диаграмма
```mermaid
erDiagram
    USERS ||--o{ BOOKINGS : makes
    RESOURCES ||--o{ BOOKINGS : reserved_for
    USERS ||--o{ REVIEWS : writes
    RESOURCES ||--o{ REVIEWS : receives
    BOOKINGS ||--o| REVIEWS : based_on

    USERS {
        bigint id PK
        string name
        string email UNIQUE
        string password
        string role
        datetime created_at
        datetime updated_at
    }

    RESOURCES {
        bigint id PK
        string name
        text description
        string type
        int capacity
        string location
        decimal price_per_hour
        bool is_active
        datetime created_at
        datetime updated_at
    }

    BOOKINGS {
        bigint id PK
        bigint user_id FK
        bigint resource_id FK
        date date
        time start_time
        time end_time
        string status
        datetime created_at
        datetime updated_at
    }

    REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint booking_id FK
        bigint resource_id FK
        int rating
        text comment
        datetime created_at
        datetime updated_at
    }
```

## Роли и доступ

| Действие | Гость | Пользователь | Администратор |
|---|---|---|---|
| Регистрация/логин | ✅ | ✅ | ✅ |
| Просмотр профиля `/auth/me` | ❌ | ✅ | ✅ |
| CRUD ресурсов | ❌ | ❌ | ✅ |
| Список ресурсов/поиск/расписание | ❌ | ✅ | ✅ |
| Создание бронирования | ❌ | ✅ | ✅ |
| Просмотр бронирований | ❌ | Только свои | Все |
| Отмена бронирования | ❌ | Только своё | Любое |
| Оставить отзыв | ❌ | ✅ (по правилам) | ✅ (по правилам) |

## Основные эндпоинты API

| Метод | URL | Доступ | Назначение |
|---|---|---|---|
| POST | `/api/auth/register` | Публичный | Регистрация пользователя |
| POST | `/api/auth/login` | Публичный | Логин и получение JWT |
| POST | `/api/auth/logout` | Авторизованный | Выход (инвалидация токена) |
| GET | `/api/auth/me` | Авторизованный | Профиль текущего пользователя |
| GET | `/api/resources` | Авторизованный | Список активных ресурсов (фильтры/сортировка/пагинация) |
| GET | `/api/resources/available` | Авторизованный | Поиск свободных ресурсов на дату/время |
| GET | `/api/resources/{resource}` | Авторизованный | Детали ресурса |
| POST | `/api/resources` | Только admin | Создание ресурса |
| PUT | `/api/resources/{resource}` | Только admin | Обновление ресурса |
| DELETE | `/api/resources/{resource}` | Только admin | Удаление ресурса |
| GET | `/api/resources/{resource}/schedule` | Авторизованный | Расписание ресурса (day/week) |
| GET | `/api/bookings` | Авторизованный | Список бронирований (admin: все, user: свои) |
| POST | `/api/bookings` | Авторизованный | Создание бронирования |
| DELETE | `/api/bookings/{id}` | Авторизованный | Отмена бронирования |
| GET | `/api/resources/{resource}/reviews` | Авторизованный | Отзывы и средний рейтинг ресурса |
| POST | `/api/resources/{resource}/reviews` | Авторизованный | Создание отзыва по бронированию |

Полный контракт со схемами запросов/ответов и ошибок: `src/public/openapi.yaml`.

## Тестовые пользователи (seed)
- Админ: `admin@cafe.com` / `password`
- Пользователь: `user@cafe.com` / `password`

## Как проверить ключевые сценарии

### 1. Запуск проекта и документации
```bash
docker-compose up --build
```
Проверка:
- `http://localhost:8000/up` -> 200
- `http://localhost:8000/docs` -> Swagger UI

### 2. Авторизация (чекпоинт 2)
```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/auth/login" -ContentType "application/json" -Body '{"email":"admin@cafe.com","password":"password"}'
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/auth/login" -ContentType "application/json" -Body '{"email":"user@cafe.com","password":"password"}'
```

Проверить в Swagger:
- `POST /api/resources` без токена -> `401`
- `POST /api/resources` от обычного пользователя -> `403`
- `POST/PUT/DELETE /api/resources` от админа -> успешные ответы

### 3. Бронирования и конфликт интервалов (чекпоинт 3)
Проверка через Swagger:
- создать бронирование (`POST /api/bookings`) -> `201`
- создать второе на пересекающееся время -> `422`
- отменить бронирование (`DELETE /api/bookings/{id}`)
- получить список бронирований (`GET /api/bookings`)

Ключевое правило пересечения:
- конфликт есть, если `existing.start < new.end` и `existing.end > new.start`.

### 4. Расписание, поиск, отзывы, рейтинг (чекпоинт 4)
Проверка через Swagger:
- `GET /api/resources/available` (поиск свободных)
- `GET /api/resources/{id}/schedule?date=YYYY-MM-DD&mode=day|week`
- `GET /api/resources` с фильтрами/сортировкой/пагинацией
- `GET /api/resources/{id}/reviews` (список + `average_rating`)
- `POST /api/resources/{id}/reviews` (срабатывают бизнес-ограничения)

### 5. Тесты, документация, упаковка (чекпоинт 5)
```bash
docker-compose exec app php artisan test
```
Покрыты критичные сценарии:
- доступ без/с токеном;
- создание бронирования;
- пересечение времени;
- смежные интервалы;
- неактивный ресурс.

## Postman коллекции
- `collections/gym-booking-checkpoint3.json`
- `collections/cafe-booking-checkpoint4.json`

## Swagger / OpenAPI
- UI: `http://localhost:8000/docs`
- YAML: `http://localhost:8000/openapi.yaml`
- Файл в репозитории: `src/public/openapi.yaml`

## Docker
Используемые файлы:
- `docker-compose.yml`
- `src/Dockerfile`
- `src/docker/entrypoint.sh`
- `src/.dockerignore`

Логика старта контейнера `app`:
- подхватывает env,
- ждёт готовности MySQL,
- выполняет `php artisan migrate:fresh --seed --force`,
- запускает API на `0.0.0.0:8000`.

## Локальный запуск без Docker 
```bash
cd src
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

## Артефакты для защиты
- Автотесты: `src/tests/Feature/BookingApiTest.php`
- OpenAPI: `src/public/openapi.yaml`
- Swagger UI: `/docs`
- Контейнеризация: `docker-compose.yml`, `src/Dockerfile`
- Коллекции для демо: `collections/*.json`
