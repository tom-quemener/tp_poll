# TP PHP - POLL

## Install dependancies

composer install

## Match database

Create .env from .env.exemple and match your database informations then run the next line :

php bin/console doctrine:schema:create

## Run the server

php bin/console server:run
