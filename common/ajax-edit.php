<?
/** Configure connect */

$host = "localhost";
$user = "lessons";
$pass = "lessons";
$dbname = "lessons";

/** Do not Edit */
$mysqli = new mysqli($host, $user, $pass, $dbname);
$mysqli->set_charset("utf8");

//Если был послан POST запрос, то выбираем данные и сохраняем
if ($_POST){

	$table = $_POST['table']; 	//таблица (получаем из #table)
	$field = $_POST['field']; 	//имя поля (получаем при разборе класса td)
	$id = $_POST['id']; 		//id ячейки которую будем обновлять (получаем при разборе класса td)
	$value = $_POST['value'];	//новое значение (получаем при разборе класса td)

	$query = "UPDATE `".$table."` SET `".$field."`='".$value."' WHERE id = '".$id."'"; //составляем запрос
	$mysqli->query($query); //выполняем запрос
	echo "Updated success"; //выводим ответ
	exit(); //завершаем работу скрипта

}

$query = "SELECT * from clients"; // запрос
$table = $mysqli->query($query); // выбираем данные из таблицы

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Ajax редактирование таблиц и табличных данных</title>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
</head>
<body>
	
	<div class="container">

		<h1 class="text-center">Ajax редактирование таблиц и табличных данных <small><a href="http://quato.com.ua/ajax-redaktirovanie-tablic-i-tablichnyx-dannyx/">&laquo; Вернуться к уроку</a></small></h1>

		<table class="table table-striped table-bordered" id="clients">
		<tbody>
			<tr>
				<th>Имя</th>
				<th>Фамилия</th>
				<th>Телефон</th>
			</tr>
			<!--Эта часть выводится с помощью php -->
			<?foreach ($table as $rows) {?>
				<tr>
					<td class="edit firstname <?=$rows['id']?>"><?=$rows['firstname']?></td>
					<td class="edit lastname <?=$rows['id']?>"><?=$rows['lastname']?></td>
					<td class="edit phone <?=$rows['id']?>"><?=$rows['phone']?></td>
				</tr>
			<?}?>
		</tbody>
		</table>

		<a href="http://quato.com.ua/lessons/ajax-edit.zip" class="btn btn-block btn-lg btn-primary">Скачать исходники</a>

	</div>

	<script>

		//при нажатии на ячейку таблицы с классом edit
		$(document).on('click', 'td.edit', function(){
			//находим input внутри элемента с классом ajax и вставляем вместо input его значение
			$('.ajax').html($('.ajax input').val());
			//удаляем все классы ajax
			$('.ajax').removeClass('ajax');
			//Нажатой ячейке присваиваем класс ajax
			$(this).addClass('ajax');
			//внутри ячейки создаём input и вставляем текст из ячейки в него
			$(this).html('<input id="editbox" size="'+ $(this).text().length+'" value="' + $(this).text() + '" type="text">');
			//устанавливаем фокус на созданном элементе
			$('#editbox').focus();
		});

		//определяем нажатие кнопки на клавиатуре
		$(document).on('keydown', 'td.edit', function(event){
		//получаем значение класса и разбиваем на массив
		//в итоге получаем такой массив - arr[0] = edit, arr[1] = наименование столбца, arr[2] = id строки
		arr = $(this).attr('class').split( " " );
		//проверяем какая была нажата клавиша и если была нажата клавиша Enter (код 13)
		   if(event.which == 13)
		   {
				//получаем наименование таблицы, в которую будем вносить изменения
				var table = $('table').attr('id');
				//выполняем ajax запрос методом POST
				$.ajax({ type: "POST",
				//в файл update_cell.php
				url:"ajax-edit.php",
				//создаём строку для отправки запроса
				//value = введенное значение
				//id = номер строки
				//field = название столбца
				//table = собственно название таблицы
				 data: "value="+$('.ajax input').val()+"&id="+arr[2]+"&field="+arr[1]+"&table="+table,
				//при удачном выполнении скрипта, производим действия
				 success: function(data){
				//находим input внутри элемента с классом ajax и вставляем вместо input его значение
				 $('.ajax').html($('.ajax input').val());
				//удаялем класс ajax
				 $('.ajax').removeClass('ajax');
				 }});
		 	}

		});

		//Сохранение при нажатии вне поля
		$(document).on('blur', '#editbox', function(){

				var arr = $('td.edit').attr('class').split( " " );
				//получаем наименование таблицы, в которую будем вносить изменения
				var table = $('table').attr('id');
				//выполняем ajax запрос методом POST
				$.ajax({ type: "POST",
				//в файл update_cell.php
				url:"ajax-edit.php",
				//создаём строку для отправки запроса
				//value = введенное значение
				//id = номер строки
				//field = название столбца
				//table = собственно название таблицы
				 data: "value="+$('.ajax input').val()+"&id="+arr[2]+"&field="+arr[1]+"&table="+table,
				//при удачном выполнении скрипта, производим действия
				 success: function(data){
				//находим input внутри элемента с классом ajax и вставляем вместо input его значение
				 $('.ajax').html($('.ajax input').val());
				//удаялем класс ajax
				 $('.ajax').removeClass('ajax');
				 }});
		});



	</script>
	
</body>
</html>