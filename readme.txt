Habilitar extensión pgsql para pg_connect:
Busca tu archivo php.ini (la ubicación varía según tu sistema operativo y cómo instalaste PHP, pero suele estar en /etc/php/X.X/apache2/php.ini o similar para Linux, o en la carpeta de instalación de PHP para Windows). Descomenta (quita el ;) la línea que dice:

extension=pgsql

Habilitar extensión pdo_pgsql para PDO:
En el mismo php.ini, descomenta la línea:

extension=pdo_pgsql

Reinicia tu servidor web (Apache, Nginx, etc.) después de hacer cambios en php.ini para que los cambios surtan efecto.