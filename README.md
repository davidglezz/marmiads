# MarmiAds
MarmiAds Prestashop module


## Version compatibility:
- PHP 5.4+
- Prestashop 1.6+


## Technical decisions

- There are no unit tests: Prestashop 1.6 did not think about the tests. Furthermore it heavily uses the singleton design pattern.

- No composer and autoload: There are 2 reasons why composer is not used:
  1. The module has no external dependencies
  2. Prestashop 1.6 does not use namespaces, using them in this simple module (it would only be possible use namespaces in 2 files) is not appropriate. For modules with more classes it is better to use namespaces and configure psr-4 autoload in composer.

- Performance: In development, readability and simplicity have been preferred over performance. If it were necessary to obtain less response time, they could be improved a lot, but we would have a more complex and difficult to maintain code.

- Router: Prestashop 1.6 does not allow advanced routes, only a name, for example `/module/marmiads/feed` but not `/module/marmiads/feed/products`. To allow routes like `/feed/products` I have decided to include the route in a `GET` parameter, so that the `base_url` would be: `http://example.com/module/marmiads/endpoint?action=`

