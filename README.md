Перед работой необходимо установить и настроить СУБД **MySQL**.

Проверить, что *composer* установлен через команду `composer`. Если *composer* не установлен
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
touch composer
chmod u+x composer
```
Открыть редактором файл composer и добавить в него строку

*&#96;which php&#96; composer.phar $@*

Файлы *composer.phar* и *composer* можно скопировать в какую-нибудь специальную папку *bin*, например, */usr/bin*, чтобы везде можно было вызывать *composer* как `composer`, а не `./composer` и только внтури папки, где лежат файлы

В файле *.php-database-migration/environments/dev.yml* есть настройки для подключения к БД. Далее
1. Создать **пользователя** в **MySQL**, прописать его **логин** и **пароль** в файле **dev.yml** в параметрах **username** и **password**;
2. Создать **базу данных** в **MySQL**, использовать для нее название из файла **dev.yml**, что указано в параметре **database**. Если будет другое название, то указать его в файле **dev.yml**;
3. Дать полный доступ к этой **базе данных** созданному **пользователю**;
4. Указать другие параметры в файле **dev.yml**, которые не совпадают с теми, что в действительности используются для подключения к СУБД. Например, порт в параметре **port**;
5. Выполнить команду `composer install`;
6. В консоле ОС указать путь к подпапке **bin** из появившейся папки **vendor**, чтобы была доступна компанда **migrate**, т.е. для **Unix** внутри папки проекта написать
```
alias migrate="php \"`pwd`/vendor/bin/migrate\""
```
а для **Windows** написать
```
set path=%cd%\vendor\bin;%path%
```
7. В папке **configs** выполнить в консоле следующие команды:
```
migrate migrate:init dev
migrate migrate:up dev
```
Если *migrate* будет жаловаться на *driver*, значит, скорее всего не установлено расширение *pdo_mysql*

Далее нужно поставить решения из файла **package.json**, выполнив команду `npm i`

Для помощи в работе с решениями:
- **PHP ActiveRecord**: https://koenpunt.github.io/php-activerecord/, https://www.phpactiverecord.xyz/docs/;
- **php-database-migration**: https://packagist.org/packages/php-database-migration/php-database-migration;
- **js-datepicker**: https://www.npmjs.com/package/js-datepicker

При создании приложения на портале Битрикс24 необдимо выбрать следующие права доступа:
- Бизнес-процессы (**bizproc**);
- Пользователи (**user**).

Для добавления новых **действий БП** надо создать папку в *lib/bp.activities* с названием действия, которое будет приведено к camelCase-формату,
удалив разделители и сделав каждую букву после них в верхнем регистре, этот результат будет использован как сиволный код действия. В папке
действия нужно создать файл *params.php* с описанием параметров действия и *index.php* для исполнения действия. Внутри *index.php* для обращения
к полученным параметрам действия надо использовать `$this->values[<код параметра>]`, так же доступна переменная `$restAPIUnit` для работы
с **Bitrix RestAPI**

Классы автоподлючаются, если они объявлены в папке *lib/helpers/<название класса>.class.php* или в папке *lib/models/<название класса>.php*