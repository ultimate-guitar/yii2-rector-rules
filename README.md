# List of rector rules for Yii2 projects inside Mu.se

## [`Rule's list`](RULES.md)

## Useful commands
Composer install
```
docker run --rm --interactive --tty --volume "$PWD":/app composer install
```

Run tests
```
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:8.1-cli vendor/bin/phpunit
```

Run phpstan
```
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:8.1-cli vendor/bin/phpstan analyse -c phpstan.neon
```

Run fixer
```
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:8.1-cli vendor/squizlabs/php_codesniffer/bin/phpcbf --standard=PSR12 --extensions=php  --ignore=./vendor/ -p ./
```

Regenerate docs for rules
```
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:8.1-cli vendor/bin/rule-doc-generator generate src --output-file RULES.md --ansi
```
