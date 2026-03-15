# CLI-утилита для работы с очередями RabbitMQ

### Установка:
Требования:
- PHP > 7.2
- Docker

Зависимости:
```
make composer install
```

Запустить:
```
make rabbit
```
Брокер будет доступен по localhost:15672 (логин и пароль: guest)

### Использование:
Опубликовать сообщение "Test Message" в очередь "first-queue"
```
rabbit-cli publish first-queue --message="Test Message"
```

Прослушивать очередь "first-queue"
```
rabbit-cli listen first-queue
```