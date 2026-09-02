## Setup

```bash
ddev start
ddev composer install
ddev exec php bin/console doctrine:migrations:migrate --no-interaction

# Check users, roles and tokens for API requests
ddev mysql -e "SELECT * FROM users"
```

## API

API is ready to use.
