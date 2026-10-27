<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$categories = ['Website / web application','Mobile application','Desktop software','Internal business software','API / integration','Other'];
$departments = ['Engineering','Design & UI/UX','Quality Assurance','Sales & Marketing','Human Resources','Operations','Finance & Admin','Management','Other'];
$experience = ['Excellent','Good','Average','Poor','Very poor'];
$multiQuestions = [
 'problems' => ['What issues have you experienced?',['Slow performance / loading','Bugs or errors','Login / access problems','Confusing navigation','Missing features','Mobile / responsive layout issues','Data accuracy / sync issues','Security / privacy concern','Accessibility issue','No issues encountered','Other']],
 'improvements' => ['What should we improve?',['Speed and performance','User interface / visual design','Ease of use / navigation','New features','Reliability / fewer bugs','Mobile experience','Reports / exports','Notifications / communication','Integrations / automation','Documentation / help','Security / access control','Other']],
 'working_well' => ['What is working well?',['Easy to use','Fast performance','Useful features','Clear design','Reliable / stable','Helpful reports','Works well on mobile','Good integration','Not sure / nothing yet','Other']]
];
$singleQuestions = [
 'usage_frequency' => ['How often do you use this software?',['Daily','Several times a week','Weekly','Monthly','Rarely','First time']],
 'task_success' => ['How easily can you complete your main task?',['Very easily','Easily','With some difficulty','With major difficulty','Unable to complete']],
 'performance' => ['How would you rate performance and speed?',$experience],
 'usability' => ['How easy is the software to use?',$experience],
 'reliability' => ['How reliable is the software?',$experience],
 'support' => ['If you needed help, how satisfied were you with support/documentation?',['Very satisfied','Satisfied','Neutral','Dissatisfied','Not applicable']],
 'recommend' => ['Would you recommend this software to a colleague?',['Definitely','Probably','Not sure','Probably not','Definitely not']]
];
$errors=[]; $successRef=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    // Honeypot field: bots often fill every field. Quietly reject if it is populated.
    if (trim((string)($_POST['website_url'] ?? '')) !== '') {
        $errors[] = 'We could not accept this submission. Please try again.';
    }
    // Basic per-session cooldown to discourage accidental rapid repeats.
    $now=time();
    if (!isset($_SESSION['public_feedback_last']) || $now-(int)$_SESSION['public_feedback_last'] >= 15) {
        $category=(string)($_POST['category']??'Other');
        $priority=(string)($_POST['priority']??'Normal');
        $subject=trim((string)($_POST['subject']??''));
        $message=trim((string)($_POST['message']??''));
        $suggestion=trim((string)($_POST['suggestion']??''));
        $department=(string)($_POST['department']??'');
        $softwareName=trim((string)($_POST['software_name']??''));
        $softwareVersion=trim((string)($_POST['software_version']??''));
        $rating=($_POST['rating']??'')!==''?(int)$_POST['rating']:null;
        $answers=[];
        foreach($multiQuestions as $key=>$q){$vals=$_POST[$key]??[];if(!is_array($vals))$vals=[];$answers[$key]=array_values(array_intersect(array_map('strval',$vals),$q[1]));}
        foreach($singleQuestions as $key=>$q){$val=(string)($_POST[$key]??'');$answers[$key]=in_array($val,$q[1],true)?$val:'';}
        $answers['other_details']=trim((string)($_POST['other_details']??''));
        if(!in_array($category,$categories,true))$errors[]='Choose a valid software type.';
        if(!in_array($priority,['Low','Normal','Medium','High'],true))$errors[]='Choose a valid priority.';
        if($subject===''||mb_strlen($subject)>160)$errors[]='Feedback title is required and must be 160 characters or fewer.';
        if(mb_strlen($message)<10||mb_strlen($message)>10000)$errors[]='Please provide at least 10 characters in the experience details.';
        if($softwareName===''||mb_strlen($softwareName)>160)$errors[]='Software / product name is required.';
        if($rating!==null&&($rating<1||$rating>5))$errors[]='Choose a valid overall rating.';
        if($department!==''&&!in_array($department,$departments,true))$errors[]='Choose a valid department.';
        if(!$errors){
            $ref=ref_code();
            $parts=['Software: '.$softwareName,'Type: '.$category];
            if($softwareVersion!=='')$parts[]='Version / module: '.$softwareVersion;
            $parts[]='Additional details: '.$message;
            if($answers['other_details']!=='')$parts[]='Other details: '.$answers['other_details'];
            $fullMessage=implode("\n",$parts);
            $json=json_encode($answers,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
            // Public link submissions are always anonymous and are never associated with a user account.
            $stmt=db()->prepare('INSERT INTO feedback(reference_code,submitted_by,is_anonymous,department,category,priority,subject,message,suggested_outcome,rating,questionnaire_json) VALUES(?,NULL,1,?,?,?,?,?,?,?,?)');
            $stmt->execute([$ref,$department?:null,$category,$priority,$subject,$fullMessage,$suggestion?:null,$rating,$json]);
            $_SESSION['public_feedback_last']=$now;
            $successRef=$ref;
        }
    } else {
        $errors[]='Please wait a few seconds before submitting another response.';
    }
}
$config = $GLOBALS['config'] ?? [];
$appName = $config['app_name'] ?? 'Cloudsoftera Feedback';
function checkedOption(string $name,string $value): bool { return isset($_POST[$name]) && is_string($_POST[$name]) && $_POST[$name]===$value; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Software Feedback | Cloudsoftera</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--ink:#111827;--muted:#667085;--line:#e4e8f0;--primary:#515ee8;--soft:#f5f7fc}*{box-sizing:border-box}body{margin:0;background:var(--soft);color:var(--ink);font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif}.top{background:#111827;color:#fff;padding:18px 0}.brand{display:flex;align-items:center;gap:12px;font-weight:800}.brand-mark{width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,#777cff,#424dc6);display:grid;place-items:center;font-size:20px}.brand small{display:block;font-size:10px;letter-spacing:1.6px;color:#b9c2e8;font-weight:500;margin-top:2px}.hero{padding:34px 0 24px}.eyebrow{font-size:11px;letter-spacing:1.8px;font-weight:800;color:var(--primary)}h1{font-size:clamp(28px,4vw,38px);font-weight:850;letter-spacing:-1.2px;margin:8px 0}.lead{color:var(--muted);font-size:14px}.cardx{background:white;border:1px solid var(--line);border-radius:18px;padding:clamp(18px,3vw,28px);box-shadow:0 8px 26px #1d2b4d08;margin-bottom:18px}.section-title{font-size:12px;font-weight:850;letter-spacing:1.3px;color:#5865e8;margin:8px 0 18px}.form-label,legend{font-size:13px;font-weight:700}.form-control,.form-select{border-color:#dce2ec;border-radius:10px;min-height:44px;font-size:14px}.form-control:focus,.form-select:focus{border-color:#7d86f0;box-shadow:0 0 0 .2rem #515ee81b}.question{border:1px solid var(--line);border-radius:14px;padding:16px;margin-bottom:12px}.question legend{float:none;width:auto;margin:0 0 12px}.choices{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.choice{display:flex;gap:9px;align-items:flex-start;border:1px solid #e5e9f0;border-radius:10px;padding:10px;font-size:13px;cursor:pointer;line-height:1.4}.choice:has(input:checked){background:#f2f3ff;border-color:#6c76ed;color:#303bb5}.choice input{margin-top:3px;accent-color:var(--primary)}.rating{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:5px}.rating input{display:none}.rating label{font-size:30px;color:#d5d9e3;cursor:pointer}.rating input:checked~label,.rating label:hover,.rating label:hover~label{color:#f2b84b}.btn-primary{background:var(--primary);border-color:var(--primary);border-radius:10px;font-weight:750;padding:12px 20px}.side{position:sticky;top:18px}.hint{background:#f5f6ff;border:1px solid #e4e6ff;padding:16px;border-radius:14px;font-size:13px;color:#4c55a6}.footer{font-size:12px;color:#8a94a8;padding:20px 0 32px}.hp{position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden}@media(max-width:575px){.choices{grid-template-columns:1fr}.hero{padding-top:26px}}
</style>
</head>
<body>
<header class="top"><div class="container d-flex justify-content-between align-items-center"><div class="brand"><div class="brand-mark">C</div><div>cloudsoftera<small>SOFTWARE FEEDBACK</small></div></div><span class="badge rounded-pill text-bg-success bg-opacity-25 border border-success-subtle text-light px-3 py-2">Public feedback link</span></div></header>
<main class="container pb-4">
<div class="hero"><div class="eyebrow">YOUR EXPERIENCE MATTERS</div><h1>Help us improve our software</h1><p class="lead mb-0">No account or login is required. Share your experience, report an issue, or suggest an improvement.</p></div>
<?php if($successRef): ?>
<div class="cardx text-center py-5"><div style="font-size:48px;color:#16a34a">✓</div><h2 class="h3 fw-bold mt-3">Thank you for your feedback!</h2><p class="text-secondary">Your response has been recorded anonymously.</p><div class="hint d-inline-block mb-3">Reference number: <strong><?= e($successRef) ?></strong><br><small>Please save this reference for your records.</small></div><div><a class="btn btn-primary" href="<?= e(url('public_feedback.php')) ?>">Submit another response</a></div></div>
<?php else: ?>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" id="publicFeedbackForm">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<div class="hp" aria-hidden="true"><label>Leave this field empty<input name="website_url" tabindex="-1" autocomplete="off"></label></div>
<div class="row justify-content-center"><div class="col-12 col-lg-10 col-xl-9">
<div class="cardx"><div class="section-title">01 / SOFTWARE DETAILS</div><div class="row g-3">
<div class="col-md-6"><label class="form-label">Software / product name *</label><input class="form-control" name="software_name" required maxlength="160" value="<?= e($_POST['software_name']??'') ?>" placeholder="e.g. Cloudsoftera TMS"></div>
<div class="col-md-6"><label class="form-label">Software type *</label><select class="form-select" name="category" required><?php foreach($categories as $c): ?><option value="<?= e($c) ?>" <?= ($_POST['category']??'Website / web application')===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Version / module</label><input class="form-control" name="software_version" maxlength="160" value="<?= e($_POST['software_version']??'') ?>" placeholder="Optional"></div>
<div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department"><option value="">Prefer not to say</option><?php foreach($departments as $d): ?><option <?= ($_POST['department']??'')===$d?'selected':'' ?>><?= e($d) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Short summary *</label><input class="form-control" name="subject" required maxlength="160" value="<?= e($_POST['subject']??'') ?>" placeholder="Briefly describe your feedback"></div>
</div></div>
<div class="cardx"><div class="section-title">02 / YOUR EXPERIENCE</div>
<?php foreach($singleQuestions as $key=>$q): ?><fieldset class="question"><legend><?= e($q[0]) ?></legend><div class="choices"><?php foreach($q[1] as $opt): ?><label class="choice"><input type="radio" name="<?= e($key) ?>" value="<?= e($opt) ?>" <?= checkedOption($key,$opt)?>> <span><?= e($opt) ?></span></label><?php endforeach; ?></div></fieldset><?php endforeach; ?>
</div>
<div class="cardx"><div class="section-title">03 / ISSUES & IMPROVEMENTS</div>
<?php foreach($multiQuestions as $key=>$q): ?><fieldset class="question"><legend><?= e($q[0]) ?></legend><div class="choices"><?php foreach($q[1] as $opt): ?><label class="choice"><input type="checkbox" name="<?= e($key) ?>[]" value="<?= e($opt) ?>" <?= isset($_POST[$key])&&is_array($_POST[$key])&&in_array($opt,$_POST[$key],true)?'checked':'' ?>> <span><?= e($opt) ?></span></label><?php endforeach; ?></div></fieldset><?php endforeach; ?>
</div>
<div class="cardx"><div class="section-title">04 / RATING & DETAILS</div><div class="row g-3">
<div class="col-md-6"><label class="form-label d-block">Overall satisfaction (optional)</label><div class="rating"><?php for($i=5;$i>=1;$i--): ?><input id="rate<?= $i ?>" type="radio" name="rating" value="<?= $i ?>" <?= (string)($_POST['rating']??'')===(string)$i?'checked':'' ?>><label for="rate<?= $i ?>">★</label><?php endfor; ?></div><small class="text-secondary">1 = very dissatisfied · 5 = very satisfied</small></div>
<div class="col-md-6"><label class="form-label">Priority</label><select class="form-select" name="priority"><?php foreach(['Normal','Low','Medium','High'] as $p): ?><option <?= ($_POST['priority']??'Normal')===$p?'selected':'' ?>><?= e($p) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Describe your experience or issue *</label><textarea class="form-control" name="message" rows="4" minlength="10" maxlength="10000" required placeholder="What happened? What did you expect? Include steps to reproduce an issue if useful."><?= e($_POST['message']??'') ?></textarea><small class="text-secondary">Please do not include passwords or sensitive customer information.</small></div>
<div class="col-12"><label class="form-label">Other details</label><textarea class="form-control" name="other_details" rows="2" maxlength="3000" placeholder="Device, browser, error message or context (optional)"><?= e($_POST['other_details']??'') ?></textarea></div>
<div class="col-12"><label class="form-label">What change would help most?</label><textarea class="form-control" name="suggestion" rows="3" maxlength="5000" placeholder="Suggest a fix, feature, or improvement (optional)"><?= e($_POST['suggestion']??'') ?></textarea></div>
</div><div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" type="submit">Submit feedback securely ↗</button></div></div>
</div></div>
</form>
<?php endif; ?>
<div class="footer text-center">© <?= date('Y') ?> Cloudsoftera · Software feedback · Internal use</div>
</main>
</body></html>
