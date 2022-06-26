# Whiskey Dex

Because I want a way to track my whiskey collection and whiskey I have tasted and want to get a bottle of!


## Hosting

DigitalOcean App Platform, managed PostgreSQL, and Spaces for object storage.

### Local object storage

Min.IO is an open source compatible S3 storage solution. However, it doesn't treat directories the same as S3 or
DigitalOcean's spaces. So local usage is difficult.

## PHPUnit + code coverage

PHPUnit is setup to calculate code coverage for the custom module.

## PHPCS

Customized PHPCS.

Stuck using `@phpstan-` prefixes for params and returns until drupal/coder is updated.
