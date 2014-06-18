laborautonomo-site
==================

Sítio/portafolio desarrollado en Silex micro-framework PHP y tema utilizando HTML5 Boilerplate.

Utiliza
-------
* [PHP >= 5.3](https://php.net)
* [Silex Micro-framework](http://silex.sensiolabs.org/)
* [html5-boilerplate](https://github.com/h5bp/html5-boilerplate)
* [GitHub API](https://developer.github.com/v3/)
* [xliff-file-generator](https://github.com/laborautonomo/xliff-file-generator)
* [Virtaal translate](https://github.com/translate/virtaal)

Instalación y Uso
----------------

1. Ejecutar `git clone https://github.com/laborautonomo/laborautonomo-site.git`

2. Haga download del [`composer.phar`](https://getcomposer.org/composer.phar) o utilizar la consola:

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

3. Ejecutar el composer: `php composer.phar install`

4. Configurar el archivo `src/bootstrap.php`

5. Agregue permiso de escritura (777) al directório "storage"

### Actualizando las páginas de cada proyecto
Las páginas de los proyectos son generadas automaticamente, con base en el archivo `README.md` de GitHub. Cuando ocurre el primero acesso a la página de uno proyecto, el archivo html va ser generado y grabado en lo directório `storage/pages/`. 
Caso quieras hacer la actualización de las páginas independiente del acesso, utilize el script `src/cron-data-updates.php` como una tarea Cron (sólo una sugerência), verifique la frequencia que necesitas llamar ese script (nosotros lo llamamos una vez a cada semana).

Ese script verifica y actualiza el contenido de Releases también.