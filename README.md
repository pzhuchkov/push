# Word stats

Сбор статистики по вхождению слов в файлах.

## Запуск

1. Запуск докер контейнера, накатывание миграций, запуск команд

```bash
make up # Запуск докера
make php # Вход в докер
make clear_setup # Создание БД
sf push:stat:watch /var/www/symfony/var/tmp/ # Запуск команда по слежению за файлами
sf push:stat:update # Запуск команды по просчету файлов(можно несколько процессов запустить
```
    
2. Создание локального домена (Песочница API доступна по http://push.local/api/doc)

```bash  
sudo echo $(docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+') "push.local" >> /etc/hosts
```

## Команды


Команды слушателя - которая следит через inotify за созданием и зменением файлов.
Далее шлет сообщение в очередь(редис).

```bash
sf push:stat:watch -h
Usage:
  push:stat:watch [<directory>]

Arguments:
  directory             Директория за которой нужно следить

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The environment name [default: "dev"]
      --no-debug        Switches off debug mode
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Команда следит за изменениеями в директории, и закидывает задание в редис
```
    
Команды калькуляции - которая следит за сообщениями в очереди, и осуществляет просчет(добавление/создание)

```bash
sf push:stat:watch -h
Usage:
  push:stat:watch [<directory>]

Arguments:
  directory             Директория за которой нужно следить

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The environment name [default: "dev"]
      --no-debug        Switches off debug mode
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Команда следит за изменениеями в директории, и закидывает задание в редис
```