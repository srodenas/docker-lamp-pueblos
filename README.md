

# docker-lamp

Docker with Apache, MySQL 8.0, PHPMyAdmin and PHP.

I use docker-compose as an orchestrator. To run these containers:

```
docker-compose up -d
```

Open phpmyadmin at [http://127.0.0.1](http://127.0.0.1)


- `docker-compose exec db mysql -u root -p` 

Infrastructure as code!

You can read this a Spanish article in Crashell platform: [Apache, PHP, MySQL y PHPMyAdmin con Docker LAMP](https://www.crashell.com/estudio/apache_php_mysql_y_phpmyadmin_con_docker_lamp).


### Infrastructure model

![Infrastructure model](.infragenie/infrastructure_model.png)

``` 
Remember. 
http: localhost:80
mysqladmin: localhost:8000
mysql: 3306
last modified. 9/03 by santi
```