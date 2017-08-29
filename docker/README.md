# Docker on MACOS

Нативный докер очень плохо монтирует директории.
Для того чтобы не было большой нагрузки на CPU нужно использовать docker-sync

https://duske.me/performant-docker-container-sync-with-docker-sync/

Нужно установить docker-sync + следящие тулзы

```bash
# install unison or rsync
brew install unison  
brew install rsync

# install fswatch to check for file changes
brew install fswatch

# install docker-sync
gem install docker-sync  
```

Длаее нужно создать файл docker-sync.yml
где указать параметры volume которые нужно монтировать.
Далее можно запустить

```bash
docker-sync start && docker-compose up
```

или 

```bash
docker-sync-stack start
```