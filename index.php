<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/app/bootstrap.php';

if (!isset($_SESSION['_token'])) $_SESSION['_token'] = bin2hex(random_bytes(32));
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$platforms = ['YouTube', 'Instagram', 'TikTok', 'آپارات', 'تلگرام', 'وب‌سایت', 'سایر'];
$statusLabels = ['idea'=>'ایده','writing'=>'در حال نگارش','producing'=>'در حال تولید','ready'=>'آماده انتشار','published'=>'منتشرشده','paused'=>'متوقف'];
$page = (string)($_GET['page'] ?? 'dashboard');
if (!in_array($page, ['dashboard','ideas','content','settings'], true)) $page = 'dashboard';
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['_token'], (string)($_POST['_token'] ?? ''))) {
        http_response_code(403); exit('درخواست معتبر نیست. صفحه را تازه‌سازی کن.');
    }
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'add_idea') {
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title !== '') {
            $stmt = $pdo->prepare('INSERT INTO ideas (title, platform, notes) VALUES (?, ?, ?)');
            $stmt->execute([mb_substr($title, 0, 180), (string)($_POST['platform'] ?? 'YouTube'), trim((string)($_POST['notes'] ?? ''))]);
            $_SESSION['flash'] = 'ایده با موفقیت ذخیره شد.';
        }
        header('Location: index.php?page=ideas'); exit;
    }
    if ($action === 'delete_idea') {
        $stmt = $pdo->prepare('DELETE FROM ideas WHERE id = ?'); $stmt->execute([(int)($_POST['id'] ?? 0)]);
        $_SESSION['flash'] = 'ایده حذف شد.'; header('Location: index.php?page=ideas'); exit;
    }
    if ($action === 'add_content') {
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title !== '') {
            $status = (string)($_POST['status'] ?? 'idea');
            if (!isset($statusLabels[$status])) $status = 'idea';
            $date = trim((string)($_POST['scheduled_at'] ?? ''));
            $date = $date === '' ? null : str_replace('T', ' ', $date) . (strlen($date) === 16 ? ':00' : '');
            $stmt = $pdo->prepare('INSERT INTO content_items (title, platform, kind, status, scheduled_at, description) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([mb_substr($title, 0, 180), (string)($_POST['platform'] ?? 'YouTube'), (string)($_POST['kind'] ?? 'ویدئوی بلند'), $status, $date, trim((string)($_POST['description'] ?? ''))]);
            $_SESSION['flash'] = 'محتوا ثبت شد.';
        }
        header('Location: index.php?page=content'); exit;
    }
    if ($action === 'update_status') {
        $status = (string)($_POST['status'] ?? 'idea');
        if (isset($statusLabels[$status])) {
            $stmt = $pdo->prepare('UPDATE content_items SET status = ? WHERE id = ?');
            $stmt->execute([$status, (int)($_POST['id'] ?? 0)]);
            $_SESSION['flash'] = 'وضعیت محتوا به‌روزرسانی شد.';
        }
        header('Location: index.php?page=content'); exit;
    }
}
$flash = (string)($_SESSION['flash'] ?? ''); unset($_SESSION['flash']);
$stats = [
 'ideas'=>(int)$pdo->query('SELECT COUNT(*) FROM ideas')->fetchColumn(),
 'content'=>(int)$pdo->query('SELECT COUNT(*) FROM content_items')->fetchColumn(),
 'ready'=>(int)$pdo->query("SELECT COUNT(*) FROM content_items WHERE status='ready'")->fetchColumn(),
 'published'=>(int)$pdo->query("SELECT COUNT(*) FROM content_items WHERE status='published'")->fetchColumn()
];
function navLink(string $key, string $label, string $page): string {
    return '<a class="'.($key === $page ? 'active' : '').'" href="index.php?page='.e($key).'">'.$label.'</a>';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Creator Studio | استودیو محتوا</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <a class="brand" href="index.php"><span class="brand-mark">✦</span><span><strong>Creator Studio</strong><small>فضای مدیریت خلاقیت</small></span></a>
  <div class="workspace"><span class="workspace-dot"></span><span><b>فضای کاری من</b><small>نسخهٔ شخصی</small></span></div>
  <p class="nav-caption">فضای کار</p>
  <nav><?=navLink('dashboard','◫　داشبورد',$page)?><?=navLink('ideas','✳　بانک ایده‌ها',$page)?><?=navLink('content','▤　مدیریت محتوا',$page)?><?=navLink('settings','⚙　تنظیمات',$page)?></nav>
  <div class="sidebar-bottom"><span class="help-icon">?</span><span><b>Creator Studio</b><small>نسخهٔ اولیه</small></span></div>
</aside>
<main class="main">
<header class="topbar"><span class="breadcrumb">Creator Studio <span>/</span> <?=e(['dashboard'=>'داشبورد','ideas'=>'بانک ایده‌ها','content'=>'مدیریت محتوا','settings'=>'تنظیمات'][$page])?></span><span class="date-chip"><?=e(date('Y/m/d'))?></span></header>
<div class="content-wrap">
<?php if ($flash !== ''): ?><div class="flash"><?=e($flash)?></div><?php endif; ?>
<?php if ($page === 'dashboard'): ?>
<section class="welcome"><div><p class="eyebrow">فضای خلاقیت تو</p><h1>سلام، خالق محتوا! <span>✦</span></h1><p>ایده‌هایت را مرتب کن و برای انتشار برنامه‌ریزی کن.</p></div><a class="btn btn-primary" href="index.php?page=content">＋ محتوای جدید</a></section>
<section class="stat-grid">
<?php foreach ([['کل ایده‌ها',$stats['ideas'],'✳','violet'],['محتواها',$stats['content'],'▤','blue'],['آماده انتشار',$stats['ready'],'◷','amber'],['منتشرشده',$stats['published'],'✓','green']] as [$label,$num,$icon,$color]): ?>
<article class="stat-card"><div class="stat-icon <?=e($color)?>"><?=e($icon)?></div><span><?=e($label)?></span><strong><?=e($num)?></strong><small>مجموع ثبت‌شده</small></article>
<?php endforeach; ?>
</section>
<div class="section-heading"><div><h2>شروع سریع</h2><p>برای ادامه یکی از بخش‌ها را انتخاب کن.</p></div></div>
<section class="quick-grid"><a class="quick-card" href="index.php?page=ideas"><div class="quick-icon violet">✳</div><h3>بانک ایده‌ها</h3><p>ایده‌های تازه را قبل از فراموش‌شدن ذخیره کن.</p><span>رفتن به ایده‌ها ←</span></a><a class="quick-card" href="index.php?page=content"><div class="quick-icon blue">▤</div><h3>برنامه‌ریزی محتوا</h3><p>عنوان، پلتفرم، وضعیت و توضیحات را ثبت کن.</p><span>مدیریت محتوا ←</span></a><a class="quick-card" href="index.php?page=settings"><div class="quick-icon green">⚙</div><h3>تنظیمات</h3><p>راهنمای نصب و اتصال پایگاه داده.</p><span>مشاهده تنظیمات ←</span></a></section>
<section class="section-heading"><div><h2>آخرین محتواها</h2><p>مواردی که اخیراً ثبت شده‌اند.</p></div><a class="text-link" href="index.php?page=content">مشاهده همه ←</a></section>
<div class="table-card"><table><thead><tr><th>عنوان</th><th>پلتفرم</th><th>نوع</th><th>وضعیت</th><th>زمان برنامه‌ریزی</th></tr></thead><tbody>
<?php $recent=$pdo->query('SELECT * FROM content_items ORDER BY created_at DESC LIMIT 5')->fetchAll(); if (!$recent): ?><tr><td colspan="5" class="empty-cell">هنوز محتوایی ثبت نشده است.</td></tr><?php endif; ?>
<?php foreach($recent as $item): ?><tr><td><b><?=e($item['title'])?></b></td><td><?=e($item['platform'])?></td><td><?=e($item['kind'])?></td><td><span class="status status-<?=e($item['status'])?>"><?=e($statusLabels[$item['status']]??$item['status'])?></span></td><td><?=e($item['scheduled_at']?:'—')?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php elseif ($page === 'ideas'): ?>
<section class="page-heading"><div><p class="eyebrow">جایی برای فکرهای تازه</p><h1>بانک ایده‌ها <span>✳</span></h1><p>ایده‌هایت را ثبت کن تا بعداً به محتوای کامل تبدیل شوند.</p></div></section>
<div class="two-column"><section class="panel"><div class="panel-heading"><h2>＋ ثبت ایده جدید</h2><p>فقط عنوان اجباری است.</p></div><form method="post" class="form-stack"><input type="hidden" name="_token" value="<?=e($_SESSION['_token'])?>"><input type="hidden" name="action" value="add_idea"><label>عنوان ایده<input name="title" required maxlength="180" placeholder="مثلاً: مقایسه ابزارهای تدوین"></label><label>پلتفرم<select name="platform"><?php foreach($platforms as $p):?><option><?=e($p)?></option><?php endforeach;?></select></label><label>یادداشت<textarea name="notes" rows="4" placeholder="نکته‌ها و منابع..."></textarea></label><button class="btn btn-primary" type="submit">ذخیره ایده</button></form></section>
<section class="panel"><div class="panel-heading"><h2>ایده‌های ثبت‌شده</h2><p><?=e($stats['ideas'])?> ایده</p></div><div class="idea-list"><?php $ideas=$pdo->query('SELECT * FROM ideas ORDER BY created_at DESC')->fetchAll(); if(!$ideas):?><div class="empty-state"><div>✳</div><b>هنوز ایده‌ای اینجا نیست</b><p>از فرم کنار صفحه اولین ایده را ثبت کن.</p></div><?php endif; foreach($ideas as $idea):?><article class="idea-item"><div class="idea-symbol">✦</div><div class="idea-body"><h3><?=e($idea['title'])?></h3><p><?=e($idea['notes']?:'بدون یادداشت')?></p><small><?=e($idea['platform'])?> · <?=e($idea['created_at'])?></small></div><form method="post" data-confirm="این ایده حذف شود؟"><input type="hidden" name="_token" value="<?=e($_SESSION['_token'])?>"><input type="hidden" name="action" value="delete_idea"><input type="hidden" name="id" value="<?=(int)$idea['id']?>"><button class="icon-button danger" type="submit" aria-label="حذف ایده">×</button></form></article><?php endforeach;?></div></section></div>
<?php elseif ($page === 'content'): ?>
<section class="page-heading"><div><p class="eyebrow">از ایده تا انتشار</p><h1>مدیریت محتوا <span>▤</span></h1><p>تمام محتواهایت را در یک مکان مدیریت کن.</p></div></section>
<div class="two-column content-columns"><section class="panel"><div class="panel-heading"><h2>＋ ایجاد محتوای جدید</h2><p>مشخصات اولیه را وارد کن.</p></div><form method="post" class="form-stack"><input type="hidden" name="_token" value="<?=e($_SESSION['_token'])?>"><input type="hidden" name="action" value="add_content"><label>عنوان محتوا<input name="title" required maxlength="180" placeholder="عنوان ویدئو یا پست"></label><div class="form-row"><label>پلتفرم<select name="platform"><?php foreach($platforms as $p):?><option><?=e($p)?></option><?php endforeach;?></select></label><label>نوع محتوا<select name="kind"><?php foreach(['ویدئوی بلند','ویدئوی کوتاه','پست تصویری','استوری','پادکست','پخش زنده','مقاله'] as $kind):?><option><?=e($kind)?></option><?php endforeach;?></select></label></div><label>وضعیت<select name="status"><?php foreach($statusLabels as $key=>$label):?><option value="<?=e($key)?>"><?=e($label)?></option><?php endforeach;?></select></label><label>زمان برنامه‌ریزی<input type="datetime-local" name="scheduled_at"></label><label>توضیحات<textarea name="description" rows="3"></textarea></label><button class="btn btn-primary" type="submit">ذخیره محتوا</button></form></section>
<section class="panel"><div class="panel-heading"><h2>فهرست محتواها</h2><p><?=e($stats['content'])?> مورد</p></div><div class="content-list"><?php $items=$pdo->query('SELECT * FROM content_items ORDER BY created_at DESC')->fetchAll(); if(!$items):?><div class="empty-state"><div>▤</div><b>هنوز محتوایی ثبت نشده</b><p>از فرم کنار صفحه اولین مورد را بساز.</p></div><?php endif; foreach($items as $item):?><article class="content-item"><div class="content-thumb"><?=e($item['kind']==='پادکست'?'♫':'▶')?></div><div class="content-info"><h3><?=e($item['title'])?></h3><p><?=e($item['platform'])?> · <?=e($item['kind'])?></p><form method="post" class="status-form"><input type="hidden" name="_token" value="<?=e($_SESSION['_token'])?>"><input type="hidden" name="action" value="update_status"><input type="hidden" name="id" value="<?=(int)$item['id']?>"><select name="status" aria-label="وضعیت محتوا"><?php foreach($statusLabels as $key=>$label):?><option value="<?=e($key)?>" <?=$item['status']===$key?'selected':''?>><?=e($label)?></option><?php endforeach;?></select><button class="btn btn-small" type="submit">ذخیره</button></form></div><span class="item-date"><?=e($item['scheduled_at']?:'بدون تاریخ')?></span></article><?php endforeach;?></div></section></div>
<?php else: ?>
<section class="page-heading"><div><p class="eyebrow">تنظیمات فضای کاری</p><h1>تنظیمات <span>⚙</span></h1><p>راهنمای راه‌اندازی نسخهٔ محلی.</p></div></section><div class="settings-grid"><section class="panel"><div class="panel-heading"><h2>وضعیت سامانه</h2><p>اطلاعات اجرای فعلی</p></div><div class="setting-row"><span>پایگاه داده</span><b class="status status-published">متصل</b></div><div class="setting-row"><span>نسخه PHP</span><b><?=e(PHP_VERSION)?></b></div><div class="setting-row"><span>منطقه زمانی</span><b><?=e(date_default_timezone_get())?></b></div><div class="setting-row"><span>هوش مصنوعی API</span><b>استفاده نمی‌شود</b></div></section><section class="panel"><div class="panel-heading"><h2>راهنمای شروع</h2><p>برای اجرای محلی</p></div><ol class="guide-list"><li>Apache و MySQL را در XAMPP روشن کن.</li><li>فایل database/schema.sql را در phpMyAdmin وارد کن.</li><li>تنظیمات اتصال app/config.php را بررسی کن.</li><li>به http://localhost/creator-studio/ برو.</li></ol></section></div>
<?php endif; ?>
<footer class="footer"><span>Creator Studio</span><span>ساخته‌شده برای نظم‌دادن به خلاقیت تو ✦</span></footer>
</div></main></div>
<script src="assets/js/app.js"></script>
</body></html>