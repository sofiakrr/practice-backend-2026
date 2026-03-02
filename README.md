# Cafe

## Описание проекта

Booking API для системы бронирования столиков кафе.
Пользователи могут просматривать доступные столики и бронировать их на определённое время.
Администратор управляет столиками и имеет доступ ко всем бронированиям.

**Предметная область:** Кафе  
**Стек:** PHP 8.x, Laravel 11, MySQL  
**Аутентификация:** JWT (tymon/jwt-auth)

---

## Столики и зоны кафе

| Название | Тип | Мест | Расположение | Цена/час |
|---|---|---|---|---|
| Столик у окна | standard | 2 | Основной зал | 200 ₽ |
| Круглый столик | standard | 4 | Основной зал | 300 ₽ |
| VIP-кабинка | vip | 6 | VIP-зона | 800 ₽ |
| Столик на террасе | terrace | 4 | Терраса | 400 ₽ |
| Приватный зал | private | 15 | Отдельный зал | 1500 ₽ |

---

## Роли пользователей

## ER-диаграмма
https://dbdiagram.io/d/69a44278a3f0aa31e16fd041

# Запуск проекта

---

## Структура базы данных (ER-диаграмма)

https://dbdiagram.io/d/69a44278a3f0aa31e16fd041 

## Список эндпоинтов API

### Аутентификация

| Метод | URL | Кто может | Описание |
|---|---|---|---|
| POST | `/api/auth/register` | Все | Регистрация нового пользователя |
| POST | `/api/auth/login` | Все | Вход и получение JWT-токена |
| POST | `/api/auth/logout` | Авторизован | Выход из системы |
| GET | `/api/auth/me` | Авторизован | Данные текущего пользователя |

### Столики (ресурсы)

| Метод | URL | Кто может | Описание |
|---|---|---|---|
| GET | `/api/resources` | Авторизован | Список всех активных столиков |
| GET | `/api/resources/{id}` | Авторизован | Информация о конкретном столике |
| POST | `/api/resources` | Только admin | Создать новый столик |
| PUT | `/api/resources/{id}` | Только admin | Редактировать столик |
| DELETE | `/api/resources/{id}` | Только admin | Удалить столик |

### Бронирования

| Метод | URL | Кто может | Условия |
|---|---|---|---|
| GET | `/api/bookings` | Авторизован | User видит свои, admin — все |
| POST | `/api/bookings` | Авторизован | Создать бронирование |
| DELETE | `/api/bookings/{id}` | Авторизован | User — только своё, admin — любое |

### Расписание и отзывы

| Метод | URL | Кто может | Описание |
|---|---|---|---|
| GET | `/api/resources/{id}/schedule` | Все | Расписание столика |
| GET | `/api/resources/{id}/reviews` | Все | Отзывы о столике |
| POST | `/api/resources/{id}/reviews` | Авторизован | Оставить отзыв |

---

## Установка и запуск проекта

### Требования

- PHP >= 8.2
- Composer
- MySQL


```bash
# 1. Клонировать репозиторий
git clone https://github.com/sofiakrr/practice-backend-2026.git
cd cafe-booking

# 2. Установить зависимости
composer install

# 3. Скопировать .env
cp .env.example .env

# 4. Настроить .env — прописать данные БД
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafe_booking
DB_USERNAME=root
DB_PASSWORD=

# 5. Создать базу данных в phpMyAdmin
CREATE DATABASE cafe_booking;

# 6. Сгенерировать ключи
php artisan key:generate
php artisan jwt:secret

# 7. Подключить API-маршруты
php artisan install:api

# 8. Создать таблицы и заполнить данными
php artisan migrate:fresh --seed

# 9. Запустить сервер
php artisan serve
