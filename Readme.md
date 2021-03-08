# www_docker

Docker cluster for running my wordpress-based website (terminal.space)
Some things are hardcoded for the site, and would need to be changed if you fork this for your own website
(Pull requests are welcome to make this more generic!)

## Installation

Create a `.env` file in the root directory and add:

```
LETS_ENCRYPT_EMAIL=
```

create the following files in the /secrets directory

### acme.env

```
GANDI_LIVEDNS_KEY=
```

### backup.env

```
RESTIC_REPOSITORY=
RESTIC_PASSWORD=
B2_ACCOUNT_ID=
B2_ACCOUNT_KEY=
```

### db.env

```
MYSQL_ROOT_PASSWORD=
MYSQL_DATABASE=
MYSQL_USER=
MYSQL_PASSWORD=
```

### wp-config.php

Set up all your DB settings to match db.env. Make sure to set `define( 'DB_HOST', 'db' );`

Also add the following bit near the bottom, before the require_once

```
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS']='on';

if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = $list[0];
  }
define('WP_HOME','https://terminal.space');
define('WP_SITEURL','https://terminal.space');
define('FORCE_SSL_ADMIN', true);
```

However, if this is local development:

```
define('WP_HOME','https://localhost');
define('WP_SITEURL','https://localhost');
```

### Restoring certs and db

run `./scripts/restore_certs.sh`
run `./scripts/restore_db.sh`

Then, `docker-compose up --build`

Now, you can navigate to the site, install wordpress. Finally, restore the db from the backup

## Architecture diagram

### acme

Standalone service which updates the SSL certificates. This uses DNS to do the authentication, so no webserver is needed. Data is stored in /secrets/certs and is shared with the reverse proxy

### Reverse proxy

Main entrypoint for the application, listening to port 80 (to redirect clients to 443), and 443. It uses the certs that the [acme](#acme) service creates. It terminates the SSL connection, and then forwards it to the [www](#www) service, using docker to provide a dns entry for http://www:8080
The value of running nginx this way is it removes all of the complexity of SSL from every other service (adding on X-Forwarded-For and related headers for bits that need the real info). It also allows for seamless upgrades and migrations in the future

### www

The main webserver (nginx based). It takes files hosted in www/html and serves it back to clients. If the file is a .php file, it calls out to the unix socket (shared via the phpsocket virtual folder) to the [php](#php) service.

### php

This is running fastcgi-php (listening to the aforementioned phpsocket unix socket). It handles php code, and also has a connection to the maria [db](#db) service for holding onto data

### db

This is a standard-configuration mariadb instance, with the backing store lockated in the ./db directory

### backup

Finally, the backup service is a cron job which backs up the certs, as well as the db backup.
