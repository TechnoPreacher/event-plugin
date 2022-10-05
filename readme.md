## Событиыйный плагин v1.0


### Установка
- в админку загрузить зип-архив eventplugin.zip
![img.png](img/img.png)

- результат нажатия кнопки "Установить" будет такой:
![img_1.png](img/img_1.png)

- далее можно перейти на подпункт "Плагины->Установленные" и нажать "Активирвать": 
![img_2.png](img/img_2.png)

После этого плагин станет активным, и в боковом меню появится пункт "События :-)":
![img_4.png](img/img_4.png)


### Добавление события:

- нажав "События :-) -> Добавить" попадаем в добавление где выведен блок с кастомными полями
типа события и его даты:

![img_5.png](img/img_5.png)

после публикации все данные сохраняются и корректно отображаются в открытом редакторе.
Ну а в общем списке ""События :-) -> Все" будут видны добавленные события причём с цветовой маркировкой статуса:

![img_6.png](img/img_6.png)


### Таксономия:

- добавляется через свой пункт в меню:

![img_7.png](img/img_7.png)

Ну и при присвоении событиям всё видно:

![img_8.png](img/img_8.png)



### Виджет:

Перейдя в меню "Внешний вид - виджеты" можно добавить наш виджет:

![img_9.png](img/img_9.png)

который по умолчанию будет выводить одно события открытого типа:

![img_10.png](img/img_10.png)

щёлкнув по нему можно настроить параметры виджета:

![img_12.png](img/img_12.png)

ну и после обновления он сразу отобразится:

![img_11.png](img/img_11.png)

на странице https://palmplay.pp.ua/shop/ будет выглядеть так (за вёрстку не пинайте):

![img_13.png](img/img_13.png)

при этом просроченное (прошедшие событие отображаться не будет!)


### Шорткод:

Формат шоркода: [events numbers=4 status=closed] 

Ну и результат использования на странице https://palmplay.pp.ua/shop/ :

![img_15.png](img/img_15.png)

### Деактивация плагина:

Если его деактивировать результат на странице https://palmplay.pp.ua/shop/ будет унылым:

![img_16.png](img/img_16.png)

### Код:

Собственно код есть ещё куда вылизывать, причёсывать и оформлять:
- поправить вёрстку, 
- разрезать на несколько файлов (вынести расширенный класс в свой файл), 
- обеспечить переиспользование кода (к примеру массивов параметров в циклал WP),
- чистить БД после деактивации/удаления плагина
- ...
