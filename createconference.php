<?php 
include 'path/to/db_connect.php';
session_start();
if(isset($_SESSION['user_id'])) {
try {
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
       // set the PDO error mode to exception
       $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Допустим, в Вашей информационной системе создана таблица groups, в которой 
	// отражены данные о группах обучающихся, с полями name и id
	
	// Выбираем все группы для того, чтобы при создании конференции можно было выбрать группу 
	// или несколько групп, для которых конференция будет создаваться

	$sql = 'SELECT * FROM groups WHERE ORDER BY name';
	$stmt = $conn->prepare($sql);
	$stmt->execute(); 
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$groups = $stmt->fetchAll();
	
	// Выбираем уже созданные конференции для данного преподавателя, который определяется 
	// параметром $_SESSION['user_id']
	// таблица conferences содержит поля id, id_user - кто создал конференцию (спикер)
	// name - название конференции,
	// startdate - время начала конференции
	// room - название комнаты коференции, этот параметр генерируется сервисом arnacom, код приведен выше в скрипте
	// source - ссылка на конференцию

	$sql = 'SELECT * FROM conferences WHERE id_user = ?';
	$stmt = $conn->prepare($sql);
	$stmt->execute(); 
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$conferences = $stmt->fetchAll();
	
	if(!empty($conferences)) {
		foreach($conferences as $key=>$value) {
			$sql = 'SELECT g.* FROM groups g
			INNER JOIN groups_conferences gc ON gc.id_group = g.id
			WHERE gc.id_conference = ?';
			$stmt = $conn->prepare($sql);
			$stmt->execute([$value['id']]); 
			$stmt->setFetchMode(PDO::FETCH_ASSOC); 
			$cgroups[$key] = $stmt->fetchAll();
			$date = new DateTime($value['starttime']);
			$conferences[$key]['starttime'] = $date->format('d.m.Y H:i');
		}
	}

	}
    catch(PDOException $e)
       {
       echo $data['message'] =  "Connection failed: " . $e->getMessage(); 
       } 
} else header('Location:index.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>Конференции</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="path/to/css/font-awosome.css">
<link rel="stylesheet" href="path/to/css/bootstrap.min.css">
<link rel="stylesheet" href="path/to/css/mdb.min.css">


<script type="text/javascript" src="path/to/js/jquery.min.js"></script>
<script type="text/javascript" src="path/to/js/popper.min.js"></script>
<script type="text/javascript" src="path/to/js/bootstrap.min.js"></script>
<script type="text/javascript" src="path/to/js/mdb.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment-with-locales.js" integrity="sha512-xQokr7XOzq2ogYezmdoq13t2xcYBoKM0aZJjF91NvQX2D114fVe5yghALtQ25S+iADg4Cqzyj0P7MluW2TrgHw==" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

</head>
<body>

<div class = "container-fluid pt-3">

<!-- Форма создания конференции -->
	<form id = "createconference">
		<div class = "form-group">
			<input type = "text" name = "name" class = "form-control" placeholder = "Название" required>
		</div>
    <div class = "form-group">
			<input type = "number" name = "occupants" class = "form-control" placeholder = "Максимальное количество участников" required>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<div class="input-group date" id="datetimepicker" data-target-input="nearest">
						<input type="text" name = "datetime" required class="form-control datetimepicker-input" data-target="#datetimepicker<?php echo $key?>"/>
						<div class="input-group-append" data-target="#datetimepicker" data-toggle="datetimepicker">
							<div class="input-group-text"><i class="fa fa-calendar"></i></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class = "form-group checkbox-group required">
			<?php foreach($groups as $k=>$v) {?>
				<div class="form-check form-check-inline">
					<input class="form-check-input groups"  name = "groups[]" type="checkbox" id="inlineCheckbox<?php echo $v['id']?>" value="<?php echo $v['id']?>">
					<label class="form-check-label" for="inlineCheckbox<?php echo $v['id']?>"><?php echo $v['name']?></label>
				</div>
			<?php }?>
		</div>
		<div class = "form-group">
			<button class = "btn btn-outline-dark btn-sm createconf" data-id = "<?php echo $value['id'] ?>">Создать конференцию</button>
		</div>
	</form>

<!-- Таблица с созданными конференциями -->

	<table class = "table">
		<tbody>
		<?php if(!empty($conferences)) foreach($conferences as $key=>$value) {?>
			<tr>
				<td><?php echo $value['name']?></td>
				<td><?php echo $value['starttime']?></td>
				<td><?php foreach ($cgroups[$key] as $k=>$v) echo $v['name'].' | '?></td>
				<td><a href = "<?php echo $value['source']?>" target = "_blank">Начать конференцию</a></td>
			</tr>
		<?php }?>
		</tbody>
	</table>
</div>
<script>
$(document).ready(function(){

	$.fn.datetimepicker.Constructor.Default = $.extend({},
        $.fn.datetimepicker.Constructor.Default,
        { icons:
          { time: 'fas fa-clock',
            date: 'fas fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-arrow-circle-left',
            next: 'fas fa-arrow-circle-right',
            today: 'far fa-calendar-check-o',
            clear: 'fas fa-trash',
            close: 'far fa-times' } 
		});
						
 $(function () {
    $('.date').datetimepicker({
        locale: 'ru',
		stepping: 5,
		minDate: new Date(),
		useCurrent : true
        });
    });


function saveconference(source, room) {
	var formData = $('#createconference').serializeArray()
	formData['source'] = source
	formData['room'] = room
  
	$.ajax({
            type        : 'POST', 
            url         : 'saveconference.php', 
            data        : formData, 
	    dataType	: 'json',
            encode      : true 
        })
		.done(function(data){
			location.reload()
		})
     }

 
 
function createconference(name) {
	var formData = {
	'name' : name,
  'occupants' : occupants
	}
	$.ajax({
            type        : 'POST', 
            url         : 'https://arnacom.kz/api/create_conference_file.php', 
            data        : formData, 
		      	dataType	: 'json',
            encode      : true 
        })
         .done(function(data) {
	    if(data.success == true) {
		 saveconference(data.source, data.room)
	    } else alert(data.message)
	  })
	})
}

$(document).on('submit', '#createconference', function(e){
	e.preventDefault()
	if($('div.checkbox-group.required :checkbox:checked').length > 0) {
      let speaker =  $('input[name=name]', $(this)).val()
      let occupants = $('input[name=occupants]', $(this)).val()
      createconference(speaker, occupants)
	}
    else alert('Выберите группу')
})	
</script>
</body>
</html>
