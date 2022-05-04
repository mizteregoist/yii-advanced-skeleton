При разворачивании в докере:

```
cd project

CHANGE PORT IN docker-compose.yml FOR nginx

docker-compose up -d --build
docker-compose run --rm php composer install
docker-compose run --rm php /var/www/html/init --env=[Production|Development] --overwrite=[Yes|No]
add database connection in common/config/main-local.php
docker-compose run --rm php /var/www/html/yii migrate --migrationPath=@yii/rbac/migrations
docker-compose run --rm php /var/www/html/yii rbac/init
docker-compose run --rm php /var/www/html/yii migrate
```

При разворачивании на сервере:

```
cd project
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --filename=composer
php -r "unlink('composer-setup.php');"

1. php composer install
2. php init --env=[Production|Development] --overwrite=[Yes|No]
3. add database connection in common/config/main-local.php
3. php yii migrate --migrationPath=@yii/rbac/migrations
4. php yii rbac/init
5. php yii migrate
```

На Windows если docker занимает много места:

```
Optimize-VHD {%USER%}\AppData\Local\Docker\wsl\data\ext4.vhdx -Mode Full
```

GitLab CI/CD 
```
1. docker volume create gitlab-runner-config

2. docker run -d --name gitlab-runner --restart always \
-v /var/run/docker.sock:/var/run/docker.sock \
-v gitlab-runner-config:/etc/gitlab-runner \
gitlab/gitlab-runner:latest
```

DIRECTORY STRUCTURE
-------------------

```
docker
    config/              contains shared configurations
    logs/                contains container logs
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
