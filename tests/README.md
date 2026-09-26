# UniFlow QA

## Static checks

```bash
php tests/smoke.php
find . -name '*.php' -print0 | xargs -0 -n1 php -l
node --check public/assets/app.js
```

## Database acceptance tests

Run against a disposable MySQL database after importing `database/schema.sql` and `database/seed.sql`:

- login + rate limiting
- role permission matrix
- supervisor object-level authorization
- committee membership authorization
- course shared-program enrollment
- semester enrollment
- thesis state transitions
- graduation eligibility transaction
- document upload/download path isolation
- notification read state
- CSV report export

The production package deliberately does not ship a hard-coded production database credential; use `.env` based on `.env.example`.
