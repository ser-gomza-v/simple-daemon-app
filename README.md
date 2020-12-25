# Simple daemon application

## Установка

Находясь в директории приложения. запустить в терминале:

```
composer install --no-dev --ignore-platform-reqs
```

Заполнить конфиг config/db.php

Далее, запустить миграции: 
 
```
vendor/bin/doctrine-migrations migrations:migrate
```

Запустить сервер

```
php -S localhost:8282 -t public/
```

### При необходимости поднять базу, можно воспользоваться docker-compose:

```
docker-compose -f data/docker-compose/stack.yml up
```

Узнать ip хоста для подключения

```
docker ps
```

![docker ps](https://gyazo.com/95688e96b389254564a992f49983dc8f)

```
docker inspect <container-id>
```

![docker inspect](https://gyazo.com/eec049b52a62431916b5d3aa3cdf2ef2)



## Использование

##### Создание задачи на прогнозирование (колбэк от телеграм бота: \SimpleDaemon\Service\TelegramService::TELEGRAM_URL)

* POST /api/generate-task


```
curl --location --request POST 'localhost:8282/api/generate-task' \
--header 'Content-Type: application/json' \
--data-raw '{
    "update_id": 62954904,
    "message": {
        "message_id": 150,
        "from": {
            "id": 835529146,
            "is_bot": false,
            "first_name": "Sergey",
            "last_name": "Gomza",
            "username": "SergeyGomza",
            "language_code": "ru"
        },
        "chat": {
            "id": 835529146,
            "first_name": "Sergey",
            "last_name": "Gomza",
            "username": "SergeyGomza",
            "type": "private"
        },
        "date": 1608815543,
        "text": "$tsla"
    }
}'
```

##### Выполнение задачи планировщиком

* SET to crontab:

```
/opt/php72/bin/php /var/www/www-root/data/www/daemon/bin/console app:work 40 >/dev/null 2>&1
/opt/php72/bin/php /var/www/www-root/data/www/daemon/bin/console app:work 20 >/dev/null 2>&1
/opt/php72/bin/php /var/www/www-root/data/www/daemon/bin/console app:work >/dev/null 2>&1
```
