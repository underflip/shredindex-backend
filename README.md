# ShredIndex (Back-end)
The back-end for www.shredindex.com

# Getting started

To be added....

## Setup

A set of Docker platform is included for running the project.

### Preparation

1. Install Docker https://docs.docker.com/get-docker/
2. If not installed with Docker Desktop, Docker Compose install https://docs.docker.com/compose/install/
3. Copy `.infrastructure/docker-compose.yml.dist` to `.infrastructure/docker-compose.yml`
4. Copy `.infrastructure/.env.dist` to `.infrastructure/.env`
5. Copy `.infrastructure/etc/nginx/conf.d/default.conf.dist` to `.infrastructure/etc/nginx/conf.d/default.conf`
6. Copy `.env.dist` to `.env`
7. Mac users with M-chip will need to change image: mariadb:10.6.15 in docker-compose.yml
8. docker-compose up -d
9. cd ..
10. rm -rf vendor && docker exec shredindex-backend-php composer install

## Seeding
!Seed either the dummy data !OR google sheet data, the google sheet data is more up to date.
### Seed Dummy data
docker exec shredindex-backend-php php artisan october:migrate && docker exec shredindex-backend-php php artisan resorts:seed_test_data --fresh
### Seed Google sheet data
docker exec shredindex-backend-php php artisan october:migrate && docker exec shredindex-backend-php php artisan resorts:seed_resort_sheet_data --fresh
docker exec shredindex-backend-php php artisan resorts:seed_resort_image_sheet_data --fresh

## Indexing
### Index Resorts for Elastic Search
docker exec shredindex-backend-php php artisan resorts:index

## Testing - for testing all function
docker exec shredindex-backend-php php artisan plugin:test underflip.resorts

### Preparation Command lines
````
cp .infrastructure/docker-compose.yml.dist .infrastructure/docker-compose.yml
cp .infrastructure/.env.dist .infrastructure/.env
cp .infrastructure/etc/nginx/conf.d/default.conf.dist .infrastructure/etc/nginx/conf.d/default.conf
cp .env.dist .env
cd .infrastructure
docker-compose up -d
cd ..
rm -rf vendor && docker exec shredindex-backend-php composer install
docker exec shredindex-backend-php php artisan october:migrate && docker exec shredindex-backend-php php artisan resorts:seed_test_data --fresh
docker exec shredindex-backend-php php artisan plugin:test underflip.resorts
````
Do not update Headstart Nocio Plugin as it has been customized to handle the latest lighthouse.


### CORS issue?
php artisan config:cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

### Open in browser

If you are using the default preparation: Visit [http://localhost:8080/backend](http://localhost:8080/backend)

### IMPORTANT: Activate our Underflip theme

Visit http://localhost:8080/backend/cms/themes and click "Activate" below the "Underflip" theme.

Activating the theme ensures all pages and menus are made available to the front-end (this also impacts front-end routes).

### GraphQL

You can query the GraphQL server at `/graphql` e.g `http://localhost:8080/graphql`

An easy way to query the GraphQL server is to use the [Chrome GraphiQL extension](https://chrome.google.com/webstore/detail/graphiql-extension/jhbedfdjpmemmbghfecnaeeiokonjclb)

## Development & Testing

### Adding pages

The frontend relies on page-data from the CMS for its routes and views. For this purpose, we use an overly-opinionated combination of October CMS's CMS Page and Rainlab's Static Menu.

**Note: We do not use Rainlab's Static Pages.**

To add a new page, you can create a CMS page (be sure to make one with no content). Simply follow the out-of-the-box [Pages documentation](https://docs.octobercms.com/2.x/cms/pages.html).

**Note: Our frontend **does not** use any page-content from the CMS.**

### Total scores ("Total Shred Score")

Total scores are whenever a Resort's rating is created, saved or deleted.

#### Manually refresh total scores

Manually refresh Resort total scores with the `resorts:refresh_total_score` artisan command.

There's also a handy composer script to run this from your host machine:

```
composer resorts-refresh-total-shred-score
```

# Contributors

[Thomas Hansen](https://github.com/krank3n)
[Jackson Darlow](https://github.com/jakxnz)
[Muhammad Gifary](https://github.com/gifary)
