Перед работой необходимо установить и настроить СУБД **MySQL**. В файле *.php-database-migration/environments/dev.yml* есть настройки для подключения к БД. Далее
1. Создать **пользователя** в **MySQL**, прописать его **логин** и **пароль** в файле **dev.yml** в параметрах **username** и **password**;
2. Создать **базу данных** в **MySQL**, желательно, использовать для нее название из файла **dev.yml**, что указано в параметре **database**. Если будет другое название, то указать его в файле **dev.yml**;
3. Дать полный доступ к этой **базе данных** созданному **пользователю**;
4. Указать другие параметры в файле **dev.yml**, которые не совпадают с теми, что в действительности используются для подключения к СУБД. Например, порт в параметре **port**;
5. Выполнить команду `composer install`;
6. В консоле ОС указать путь к подпапке **bin** из появившейся папки **vendor**, чтобы была доступна компанда **migrate**;
7. внутри папки **configs** выполнить в консоле следующие команды
```
migrate migrate:init dev
migrate migrate:up dev
```

Далее нужно поставить решения из файла **package.json**, выполнив команду `npm i`

Для помощи в работе с решениями:
- **PHP ActiveRecord**: https://koenpunt.github.io/php-activerecord/, https://www.phpactiverecord.xyz/docs/ActiveRecord;
- **php-database-migration**: https://packagist.org/packages/php-database-migration/php-database-migration;
- **js-datepicker**: https://www.npmjs.com/package/js-datepicker

При создании приложения на портале Битрикс24 необдимо выбрать следующие права доступа:
- Бизнес-процессы (**bizproc**);
- Пользователи (**user**);
- CRM (**crm**)