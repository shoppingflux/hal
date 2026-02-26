# shoppingfeed/hal

## Installation

```
composer require shoppingfeed/hal
```

## Contributing

To connect to a php 8.2 container correctly configured

- Create a container : `docker run --name hal-php -v $PWD:/var/www -d ghcr.io/shoppingflux/php:8.2-unit`
- Start container : `docker start hal-php`
- Connect to container : `docker exec -it hal-php bash`

Once connected to the container you can :

- Update composer dependencies : `composer update`
- Run test : `composer test`

## Enable debugger

```bash
export XDEBUG_TRIGGER=1
export XDEBUG_CONFIG="client_host=172.17.0.1"
export PHP_IDE_CONFIG=serverName=api.shopping-feed.lan
export XDEBUG_MODE=debug
```

## Documentation

Documentation is available at [docs/index.md](docs/index.md)
