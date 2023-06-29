# b09e0f69eb66768817aa11513219bdc0
project challenge from brian

- First Please Clone this Project
- Copy Env & Edit Value disesuaikan dengan local
```
cp .env.example .env
```
- Install Composer https://getcomposer.org/download/ dan run
```
composer install
```
- Install RabbitMQ Ubuntu https://www.cherryservers.com/blog/how-to-install-and-start-using-rabbitmq-on-ubuntu-22-04
- Install Apache2 & PostgreSQL
- Set Virtual host dengan domain challenge1.local (utk keperluan test oauth)

  ROUTES:
  ```
  POST /emails
  ```
  params:
```
  message (string) - berisikan pesan yang akan dikirim ke email
```
