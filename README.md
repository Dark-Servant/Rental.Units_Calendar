#### ВНИМАНИЕ

Версия `PHP` должна быть не выше **7.4** (на *24 августа 2023 года* это **7.4.33**), лучше **7.3.27**.

Решения со стороны

- **PHP ActiveRecord**
- **php-database-migration**

больше не поддерживаются разработчиками этих решений, из-за чего будут проблемы на более новых версиях `PHP`

### Начало работы

Перед работой необходимо установить и настроить СУБД **MySQL**.

Проверить, что *composer* установлен через команду `composer`. Если *composer* не установлен
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
touch composer
chmod u+x composer
```
В ОС **Linux** Открыть редактором файл composer и добавить в него строку

*&#96;which php&#96; composer.phar $@*

Файлы *composer.phar* и *composer* можно скопировать в какую-нибудь специальную папку *bin*, например, */usr/bin*, чтобы везде можно было вызывать *composer* как `composer`, а не `./composer` внутри папки, где лежат файлы

В файле *.php-database-migration/environments/dev.yml* есть настройки для подключения к БД. Далее
1. Создать **пользователя** в **MySQL**, прописать его **логин** и **пароль** в файле **dev.yml** в параметрах **username** и **password**;
2. Создать **базу данных** в **MySQL**, использовать для нее название из файла **dev.yml**, что указано в параметре **database**. Если будет другое название, то указать его в файле **dev.yml**;
3. Дать полный доступ к этой **базе данных** созданному **пользователю**;
4. Указать другие параметры в файле **dev.yml**, которые не совпадают с теми, что в действительности используются для подключения к **СУБД**. Например, **порт** в параметре **port**;
5. Выполнить команду `composer install`;
6. Внутри главной папки в консоле ОС указать путь к подпапке **bin** из появившейся папки **vendor**, чтобы была доступна компанда **migrate**. Для этого в ОС **Linux** внутри папки проекта написать
    ```
    alias migrate="php \"`pwd`/vendor/bin/migrate\""
    ```
    а для ОС **Windows** написать
    ```
    set path=%cd%\vendor\bin;%path%
    ```

7. В папке **configs** выполнить в консоле следующие команды:
    ```
    migrate migrate:init dev
    migrate migrate:up dev
    ```
    Если *migrate* будет жаловаться на *driver*, значит, скорее всего не установлено расширение *pdo_mysql*

8. Нужно поставить решения из файла **package.json**. Заходим в папку *js/external* и выполняем команду `npm i`

Для помощи в работе с решениями:
- **PHP ActiveRecord**: https://koenpunt.github.io/php-activerecord/, https://www.phpactiverecord.xyz/docs/;
- **php-database-migration**: https://packagist.org/packages/php-database-migration/php-database-migration; (устарело, необходимо заменить)
- **js-datepicker**: https://www.npmjs.com/package/js-datepicker

### Установка как облачного приложения

При создании приложения на портале Битрикс24 необходимо выбрать следующие права доступа:
- Бизнес-процессы (**bizproc**);
- Пользователи (**user**).

### Добавление новых действий бизнес-процессов

Для добавления новых **действий БП** надо создать папку в *lib/bp.activities* с названием действия, где каждое слово отделять символом ".". При запуске приложения будет сделана проверка установлено ли дейтсвие на портале. Если хоть одно из новых действий не будет установлено, то будет выведено предложение об установке неустановленных действий. При установке действий БП название папки, в которой реализован код действия, будет использовано для получения символьного кода действия, для этого название будет приведено к camelCase-формату - символы-разделители слов (".") будут удалены, а каждая буква после них перейдет в верхний регистр. В папке действия нужно создать файл *params.php* с описанием параметров действия. Описание действия надо искать у команды **bizproc.activity.add** (https://dev.1c-bitrix.ru/rest_help/bizproc/bizproc_activity/add.php). Еще в папке должен нахожиться файл *index.php* с реализацией работы действия. Внутри *index.php* для обращения к полученным параметрам действия надо использовать `$this->values[<код параметра>]`, так же доступна переменная `$restAPIUnit` для работы с **Bitrix RestAPI**

Классы автоподлючаются, если они объявлены в папке *lib/helpers* как *<название класса>.class.php* или в папке *lib/models/<название класса>.php*. Если класс существует в папке *lib/helpers*, то при нахождении в какой-нибудь подпапке должно быть использовано `namespace`, равное значению, составленному из объединения через символ **\\** названий *папок*, начиная с *папки* после *helpers*

### Тестирование

1. На боевом сервере необходимо снять копию БД

```bash
mysqldump -u <пользователь с доступом к БД>  -p<пароль> --databases <название БД> --result-file=dmp.sql
```

2. Удалить из **dmp.sql** все запросы

```sql
CREATE DATABASE ...
```
```sql
USE ...
```
```sql
DROP TABLE `changelog`...
```
```sql
CREATE TABLE `changelog`...
```

а так же

```sql
INSERT INTO `changelog`...
INSERT INTO `chosen_technics`...
```

3. На тестовом сервере надо удалить старую БД, если такая была, и создать новую БД, далее выполнить все команды из пункта **7** в части **Начало работы**;
4. Загрузить в БД данные из поправленного **dmp.sql**.

Так же в папках *lib/bp.activities/.log/<символьный код действия БП>* сохраняются логи по каждому дейтвию БП, которые делятся минимум на три части:
- *<время в виде ГГГГММДДЧЧММСС>.log.txt*. Хранится лог с запросами и ответами для **Bitrix Rest API**;
- *<время в виде ГГГГММДДЧЧММСС>.request.txt*. Хранится информация с запросом к действию из портала;
- *<время в виде ГГГГММДДЧЧММСС>.result.txt*. Хранится результат после работы действия, его наличие как минимум означает, что действие полностью отработало;
- *<время в виде ГГГГММДДЧЧММСС>.error.txt*. Хранится возможная ошибка, сгенерированная действием. Обычно, этого файла нет.


### Баги

**!!!** Есть **баг**, в **таблицу contents БД** периодически добавляются одинаковые **записи**. Сейчас этот **баг** исправляется с помощью **скриптов**
  * *bugs\comments\badhosts\index.php*. Поправляет неправильную принадлежность *комментариев*, иначе они не отображаются в **календаре**
  * *calendar\bugs\contents\dublicates\index.php*. Ищет **дубликаты** по разным данным, включая и **контент**, заменяет их **дубликаты** и поправляет связанными с ними **данные** из **таблиц** других **моделей**

Есть общий **скрипт** *bugs\index.php*, где вызываются вышеперечисленные **скрипты**. Необходимо добавить его в **cron**.

**Альтерантивное (планируемое) решение**. Перенести работу **действий БП** для **content** и **technic** на **cron**. Для этого расширить функционал работы с **действиями БП** до возможности запуска через **cron**, для таких **действий** написать общее **действие**, которое будет собирать данные в отдельную **таблицу**, потом другой **скрипт**, запускаясь через **cron**, будет группировать запросы из этой **таблицы** по конкретным **действиям**, и запускать по запросам нужные **действия**, для таких **действий** скорее всего потребуется использовать **входящий вебхук**.