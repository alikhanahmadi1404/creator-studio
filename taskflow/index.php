<?php
declare(strict_types=1);
session_start();
$file=__DIR__.'/data/tasks.json';
if(!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data',0775,true);
if(!file_exists($file)) file_put_contents($file,json_encode(['tasks'=>[]],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
$db=json_decode((string)file_get_contents($file),true) ?: ['tasks'=>[]];
$db['tasks']=$db['tasks']??[];
function esc($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
$today=date('Y-m-d'); $week=$_GET['week']??$today;
try{$dt=new DateTimeImmutable($week);}catch(Throwable $e){$dt=new DateTimeImmutable($today);}
$n=(int)$dt->format('N'); $back=($n+1)%7; $start=$dt->modify("-$back days"); $end=$start->modify('+6 days');
$weekStart=$start->format('Y-m-d');$weekEnd=$end->format('Y-m-d');
if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=$_POST['action']??'';
 if($action==='save'){
  $title=trim($_POST['title']??'');
  if($title!==''){
   $id=trim($_POST['id']??'')?:bin2hex(random_bytes(5));
   $task=['id'=>$id,'title'=>$title,'date'=>$_POST['date']??$today,'category'=>trim($_POST['category']??'شخصی')?:'شخصی','goal'=>trim($_POST['goal']??''),'priority'=>in_array($_POST['priority']??'medium',['low','medium','high'],true)?$_POST['priority']:'medium','done'=>($_POST['done']??'0')==='1'];
   $found=false;foreach($db['tasks'] as &$old){if(($old['id']??'')===$id){$old=$task;$found=true;break;}}unset($old);
   if(!$found)$db['tasks'][]=$task;
   file_put_contents($file,json_encode($db,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);
   header('Location: ?week='.urlencode($task['date']));exit;
  }
 }elseif($action==='toggle'||$action==='delete'){
  $id=$_POST['id']??'';
  if($action==='toggle'){foreach($db['tasks'] as &$t)if(($t['id']??'')===$id)$t['done']=!($t['done']??false);unset($t);}
  else $db['tasks']=array_values(array_filter($db['tasks'],fn($t)=>($t['id']??'')!==$id));
  file_put_contents($file,json_encode($db,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);
  header('Location: ?week='.urlencode($_POST['week']??$weekStart));exit;
 }
}
$tasks=array_values(array_filter($db['tasks'],fn($t)=>($t['date']??'')>=$weekStart&&($t['date']??'')<=$weekEnd));
$done=count(array_filter($tasks,fn($t)=>!empty($t['done'])));$edit=null;
if(isset($_GET['edit']))foreach($db['tasks'] as $t)if(($t['id']??'')===$_GET['edit']){$edit=$t;break;}
$edit=$edit??['id'=>'','title'=>'','date'=>$today,'category'=>'شخصی','goal'=>'','priority'=>'medium','done'=>false];
$days=['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه'];
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow — مدیریت کارها</title><link rel="stylesheet" href="assets/style.css"></head><body>
<aside class="sidebar"><a class="brand" href="index.php"><span class="logo">✓</span><span><b>TaskFlow</b><small>مدیریت کارها</small></span></a><p class="side-label">فضای کاری</p><a class="nav active" href="index.php">▦　داشبورد هفته</a><a class="nav" href="#task-form">＋　افزودن کار</a><div class="tip"><b>قدم‌های کوچک، نتیجه‌های بزرگ</b><p>هر کاری که امروز انجام می‌دهی، تو را به هدفت نزدیک‌تر می‌کند.</p></div><small class="storage">● ذخیره‌سازی محلی فعال است</small></aside>
<main><header><div><small class="eyebrow">برنامه‌ریزی هفتگی</small><h1><?= $weekStart===(new DateTimeImmutable($today))->modify('-'.(((int)(new DateTimeImmutable($today))->format('N')+1)%7).' days')->format('Y-m-d')?'هفتهٔ جاری':'برنامهٔ هفتگی' ?></h1><p class="muted"><?= esc($weekStart) ?> تا <?= esc($weekEnd) ?></p></div><a class="today" href="?week=<?= esc($today) ?>">رفتن به امروز ↗</a></header>
<section class="stats"><article><small>کل کارهای هفته</small><strong><?= count($tasks) ?></strong><span>کار ثبت‌شده در این هفته</span></article><article><small>انجام‌شده</small><strong><?= $done ?></strong><span>آفرین، ادامه بده!</span></article><article><small>باقی‌مانده</small><strong><?= count($tasks)-$done ?></strong><span>قدم بعدی تو</span></article><article><small>درصد پیشرفت</small><strong><?= count($tasks)?round($done/count($tasks)*100):0 ?>٪</strong><div class="progress"><i style="width:<?= count($tasks)?round($done/count($tasks)*100):0 ?>%"></i></div></article></section>
<section class="panel"><div class="section-head"><div><h2>برنامهٔ این هفته</h2><p class="muted">کارهایت را بین روزهای هفته مرتب کن.</p></div><div class="controls"><a href="?week=<?= esc($start->modify('-7 days')->format('Y-m-d')) ?>">→</a><span>هفتهٔ <?= esc($start->format('W')) ?></span><a href="?week=<?= esc($start->modify('+7 days')->format('Y-m-d')) ?>">←</a></div></div><div class="week-grid">
<?php for($i=0;$i<7;$i++):$day=$start->modify("+$i days");$date=$day->format('Y-m-d');$dayTasks=array_values(array_filter($tasks,fn($t)=>($t['date']??'')===$date));?>
<div class="day <?= $date===$today?'is-today':'' ?>"><div class="dayhead"><span><?= $days[$i] ?></span><b><?= $day->format('d') ?></b></div><?php if($date===$today):?><small class="today-label">امروز</small><?php endif; ?>
<?php foreach($dayTasks as $t):?><article class="task <?= !empty($t['done'])?'done':'' ?>"><div class="tasktop"><i class="<?= esc($t['priority']??'medium') ?>"></i><span><?= esc($t['category']??'شخصی') ?></span></div><h3><?= esc($t['title']) ?></h3><?php if(!empty($t['goal'])):?><p>هدف: <?= esc($t['goal']) ?></p><?php endif; ?><div class="actions"><form method="post"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= esc($t['id']) ?>"><input type="hidden" name="week" value="<?= esc($weekStart) ?>"><button><?= !empty($t['done'])?'✓ انجام شد':'○ انجام شد؟' ?></button></form><a href="?week=<?= esc($weekStart) ?>&edit=<?= esc($t['id']) ?>#task-form">ویرایش</a><form method="post" onsubmit="return confirm('این کار حذف شود؟')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= esc($t['id']) ?>"><input type="hidden" name="week" value="<?= esc($weekStart) ?>"><button class="delete">×</button></form></div></article><?php endforeach; ?><a class="add" href="#task-form" data-date="<?= esc($date) ?>">＋ افزودن کار</a></div><?php endfor; ?></div></section>
<section class="panel form-panel" id="task-form"><div class="section-head"><div><h2><?= $edit['id']?'ویرایش کار':'کار جدید' ?></h2><p class="muted">کار بعدی‌ات را با یک هدف مشخص ثبت کن.</p></div></div><form method="post" class="taskform"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= esc($edit['id']) ?>"><label class="wide">عنوان کار *<input name="title" required placeholder="مثلاً: تمرین ریاضی" value="<?= esc($edit['title']) ?>"></label><label>روز انجام<input type="date" name="date" required value="<?= esc($edit['date']) ?>"></label><label>دسته‌بندی<input name="category" list="categories" value="<?= esc($edit['category']) ?>"><datalist id="categories"><option>شخصی</option><option>درس</option><option>کار</option><option>پروژه</option></datalist></label><label>اولویت<select name="priority"><option value="low" <?= $edit['priority']==='low'?'selected':'' ?>>کم</option><option value="medium" <?= $edit['priority']==='medium'?'selected':'' ?>>معمولی</option><option value="high" <?= $edit['priority']==='high'?'selected':'' ?>>زیاد</option></select></label><label class="wide">هدف این کار<input name="goal" placeholder="مثلاً: حل ۱۰ سؤال" value="<?= esc($edit['goal']) ?>"></label><div class="form-actions"><button class="primary"><?= $edit['id']?'ذخیره تغییرات':'＋ ثبت کار' ?></button><?php if($edit['id']):?><a href="?week=<?= esc($weekStart) ?>#task-form">لغو</a><?php endif;?></div></form></section><footer>TaskFlow · برنامه‌ریزی بهتر، روزهای آرام‌تر</footer></main><script src="assets/app.js"></script></body></html>
