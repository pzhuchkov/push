Word stats
========================

Сбор статистики по вхождению слов в файлах.

--------------

Запуск
========================

    ```bash
    $ docker-compose build
    $ docker-compose up -d
    $ docker-compose php
    $ make clear_setup
    ```
    
Создание локального домена

    ```bash  
    $ sudo echo $(docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+') "push.local" >> /etc/hosts
    ```
    
Создание БД + Схемы

    ```bash
            $ sf3 doctrine:database:create
            $ sf3 doctrine:schema:update --force
    ```
    
Запуск команды слушателя

    ```bash
sf push:stat:watch <directory_name>
    ```