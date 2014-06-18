laborautonomo-site
==================

This is a open source website/portfolio developed in Silex Micro-framework PHP with HTML5 Boilerplate Theme.

Using
-----
* [PHP >= 5.3](https://php.net)
* [Silex Micro-framework](http://silex.sensiolabs.org/)
* [html5-boilerplate](https://github.com/h5bp/html5-boilerplate)
* [GitHub API](https://developer.github.com/v3/)
* [xliff-file-generator](https://github.com/laborautonomo/xliff-file-generator)
* [Virtaal translate](https://github.com/translate/virtaal)

Installation / Usage
--------------------

1. Run `git clone https://github.com/laborautonomo/laborautonomo-site.git`

2. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

3. Run Composer: `php composer.phar install`

4. Configure file `src/bootstrap.php`

5. Write permission to "storage" directory

### Updating project's page
The project's page is generated based in GitHub's readme. When you make first access of a project page, the html is generated. But, to update this generated file, call `src/cron-data-updates.php` file using task Cron (as a suggestion). 
Verify the necessary frequency (we call once a week).

This script also updates the Releases of projects.