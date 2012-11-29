# INSTALLATION

1. Create DB. Pull SQL dump from db/cv.sql
2. By default in DB user 'admin' with password 'admin' will be added
3. Create application.ini file in application/configs. You can use application.ini.sample as an example. Put your own credentials there.

## APACHE

Apache VirtualHost example:

```apache
<VirtualHost *:80>
  ServerName cvapp
  DocumentRoot /var/www/cv-application/public/
  SetEnv APPLICATION_ENV development
  ErrorLog /var/log/apache2/cv.log
  <Directory /var/www/cv-appllication/public/>
       Options Indexes FollowSymLinks MultiViews
       AllowOverride All
       Order allow,deny
       allow from all
  </Directory>
</VirtualHost>
```

## LDAP

If you want to use LDAP set ldap.status = 1 in application.ini