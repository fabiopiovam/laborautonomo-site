laborautonomo-site
==================

Website/portfolio desenvolvido com Silex micro-framework PHP e tema responsivo utilizando HTML5 Boilerplate.

Utiliza
-------
* [PHP >= 5.3](https://php.net)
* [Silex Micro-framework](http://silex.sensiolabs.org/)
* [html5-boilerplate](https://github.com/h5bp/html5-boilerplate)
* [GitHub API](https://developer.github.com/v3/)
* [xliff-file-generator](https://github.com/laborautonomo/xliff-file-generator)
* [Virtaal translate](https://github.com/translate/virtaal)

Instalação e Uso
----------------

1. Execute `git clone https://github.com/laborautonomo/laborautonomo-site.git`

2. Baixe o executável [`composer.phar`](https://getcomposer.org/composer.phar) ou utilize o instalador:

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

3. Execute o composer: `php composer.phar install`

4. Configure o arquivo `src/bootstrap.php`

5. Adicione permissão de escrita (777) ao diretório "storage"

### Atualizando as páginas de cada projeto
As páginas dos projetos são geradas automaticamente, baseadas no arquivo `README.md` do GitHub. Quando ocorre o primeiro acesso à página de um projeto, o arquivo html é gerado e gravado no diretório `storage/pages/`.
Caso você prefira fazer a atualização das páginas dos projetos independente do acesso, utilize o script `src/cron-data-updates.php` em uma tarefa Cron (isso é apenas uma sugestão), verificando a frequencia necessária (nós chamamos uma vez por semana).

Esse script verifica e atualiza o conteúdo de Releases também.