
Installation
------------

```bash
$ git clone git@github.com:raphagoo/blog-tp-back.git
```


```bash
$ composer install
```

```bash
$ php bin/console doctrine:database:create
```

```bash
$ php bin/console make:migration
```

```bash
$ php bin/console doctrine:migrations:migrate
```

```bash
$ php bin/console doctrine:fixtures:load
```

```bash
$ symfony server:start
```
