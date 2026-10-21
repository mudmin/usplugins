<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > configure: saving settings and triaging reports. Included by configure.php after
// the master-account and CSRF checks; ends with a redirect back to the tab (post/redirect/get).

$hrS = handrailSettings();
$hrAction = (string) Input::get('hr_action');
$hrBack = $hrSelf . '&tab=' . $hrTab;
$hrPost = function ($k, $default = '') {
    return isset($_POST[$k]) && is_string($_POST[$k]) ? trim(Input::get($k)) : $default;
};
// Raw values for fields where Input::get's HTML encoding would change what was typed. They are
// only ever stored and printed back through safeReturn(), never into HTML unescaped.
$hrRaw = function ($k, $max) {
    return isset($_POST[$k]) && is_string($_POST[$k]) ? mb_substr(trim(str_replace("\0", '', $_POST[$k])), 0, $max) : '';
};
$hrPick = function ($k, $allowed, $default) use ($hrPost) {
    $v = $hrPost($k);
    return in_array($v, $allowed, true) ? $v : $default;
};
$hrBool = function ($k) {
    return isset($_POST[$k]) && $_POST[$k] === '1' ? 1 : 0;
};
$hrInt = function ($k, $min, $max, $default) use ($hrPost) {
    $v = $hrPost($k);
    return is_numeric($v) ? max($min, min($max, (int) $v)) : $default;
};
$hrErrors = [];
$hrDone = '';

switch ($hrAction) {
    case 'save_look':
        $hrS['style'] = $hrPick('style', array_keys(handrailStyles()), 'sheet');
        $hrS['floating'] = $hrBool('floating');
        $hrS['corner'] = $hrPick('corner', ['br', 'bl', 'tr', 'tl'], 'br');
        $hrS['offset_x'] = $hrInt('offset_x', 0, 400, 16);
        $hrS['offset_y'] = $hrInt('offset_y', 0, 400, 16);
        $hrS['size'] = $hrPick('size', ['small', 'medium', 'large'], 'medium');
        $hrS['label'] = $hrPick('label', ['icon', 'text', 'tab', 'handle'], 'icon');
        $hrS['icon'] = $hrPick('icon', array_keys(handrailIcons()), 'person');
        $hrS['hide_on_mobile'] = $hrBool('hide_on_mobile');
        $hrS['on_dashboard'] = $hrBool('on_dashboard');
        $hrAccent = handrailHex($hrPost('brand_accent'));
        // The panel uses the accent as text and icons on its light surfaces (#f3f5f9 is the
        // darker of the two), and as the button background behind black or white text.
        if ($hrAccent && handrailRatio($hrAccent, '#f3f5f9') < 4.5) {
            $hrErrors[] = 'That accent color is too light: it reaches only ' . number_format(handrailRatio($hrAccent, '#f3f5f9'), 1)
                . ':1 against the panel background, and links and icons in the panel need 4.5:1. Pick a darker color. The rest was saved.';
            $hrAccent = '';
        } elseif ($hrPost('brand_accent') !== '' && !$hrAccent) {
            $hrErrors[] = 'The accent color must look like #0b6e69. The rest was saved.';
        }
        $hrS['brand_accent'] = $hrAccent;
        $hrS['brand_title'] = $hrRaw('brand_title', 60);
        $hrS['brand_button'] = $hrRaw('brand_button', 40);
        $hrDone = 'Look and placement saved.';
        break;

    case 'save_tools':
        $hrS['tools'] = array_values(array_intersect(array_keys(handrailTools()), (array) ($_POST['tools'] ?? [])));
        $hrPresetIds = array_map('strval', array_keys(handrailPresets()));
        $hrS['presets'] = array_values(array_intersect($hrPresetIds, (array) ($_POST['presets'] ?? [])));
        $hrTools = handrailTools();
        $hrCustom = [];
        foreach (array_slice((array) ($_POST['custom'] ?? []), 0, HANDRAIL_MAX_PRESETS) as $hrP) {
            if (!is_array($hrP)) {
                continue;
            }
            $hrLabel = mb_substr(trim(str_replace("\0", '', (string) ($hrP['label'] ?? ''))), 0, 40);
            $hrMap = [];
            foreach ((array) ($hrP['tools'] ?? []) as $hrId => $hrStep) {
                $hrStep = (int) $hrStep;
                if (isset($hrTools[$hrId]) && $hrStep > 0 && $hrStep <= count($hrTools[$hrId]['steps'])) {
                    $hrMap[$hrId] = $hrStep;
                }
            }
            // The reading guide and mask can't run together; keep the mask.
            if (isset($hrMap['guide'], $hrMap['mask'])) {
                unset($hrMap['guide']);
            }
            if ($hrLabel !== '' && $hrMap) {
                $hrCustom[] = ['label' => $hrLabel, 'desc' => mb_substr(trim(str_replace("\0", '', (string) ($hrP['desc'] ?? ''))), 0, 80), 'tools' => $hrMap];
            } elseif ($hrLabel !== '' && !$hrMap) {
                $hrErrors[] = '"' . $hrLabel . '" was not saved: pick at least one tool for it.';
            }
        }
        $hrS['custom_presets'] = $hrCustom;
        // New custom quick settings are shown straight away.
        $hrS['presets'] = array_values(array_filter($hrS['presets'], function ($id) {
            return strpos($id, 'custom-') !== 0;
        }));
        foreach (array_keys($hrCustom) as $hrI) {
            $hrS['presets'][] = 'custom-' . $hrI;
        }
        $hrDone = 'Tools and quick settings saved.';
        break;

    case 'save_reports':
        $hrS['reports'] = $hrPick('reports', ['off', 'logged_in', 'everyone'], 'off');
        $hrEmail = $hrPost('report_email');
        if ($hrEmail !== '' && !filter_var($hrEmail, FILTER_VALIDATE_EMAIL)) {
            $hrErrors[] = 'The notification email does not look right, so reports will go to the first master account.';
            $hrEmail = '';
        }
        $hrS['report_email'] = $hrEmail;
        $hrS['notify'] = $hrPick('notify', ['each', 'none'], 'each');
        $hrS['report_contact'] = $hrBool('report_contact');
        $hrS['retention_days'] = $hrInt('retention_days', 0, 3650, 365);
        $hrS['captcha_provider'] = $hrPick('captcha_provider', array_keys(handrailCaptchaProviders()), 'none');
        $hrSources = ['manual'];
        foreach (handrailCaptchaDetect() as $hrRow) {
            $hrSources[] = $hrRow['source'];
        }
        $hrS['captcha_source'] = $hrPick('captcha_source', $hrSources, 'manual');
        $hrSite = $hrPost('captcha_sitekey');
        $hrSecret = $hrPost('captcha_secret');
        foreach ([$hrSite, $hrSecret] as $hrKey) {
            if ($hrKey !== '' && !preg_match('/^[A-Za-z0-9_\-.]{1,255}$/D', $hrKey)) {
                $hrErrors[] = 'Captcha keys may contain only letters, numbers, dots, underscores and hyphens.';
                $hrSite = $hrS['captcha_sitekey'];
                $hrSecret = '';
                break;
            }
        }
        $hrS['captcha_sitekey'] = $hrSite;
        // A blank secret keeps the saved one, so it never has to be printed back into the page.
        if ($hrSecret !== '') {
            $hrS['captcha_secret'] = $hrSecret;
        }
        if ($hrBool('clear_secret')) {
            $hrS['captcha_secret'] = '';
        }
        $hrScore = $hrPost('captcha_v3_score');
        $hrS['captcha_v3_score'] = is_numeric($hrScore) ? max(0.1, min(0.9, round((float) $hrScore, 1))) : 0.5;
        if ($hrS['captcha_provider'] !== 'none' && $hrS['captcha_source'] === 'manual' && ($hrS['captcha_sitekey'] === '' || $hrS['captcha_secret'] === '')) {
            $hrErrors[] = 'The captcha is not on yet: enter both the site key and the secret key, or pick keys found on this site.';
        }
        $hrDone = 'Report settings saved.';
        break;

    case 'save_check':
        $hrS['check'] = $hrBool('check');
        $hrPerms = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['check_perms'] ?? [])))));
        if ($hrS['check'] && !$hrPerms) {
            $hrErrors[] = 'Pick at least one permission level, or turn the checker off. Administrators were kept.';
            $hrPerms = [2];
        }
        $hrS['check_perms'] = $hrPerms ?: [2];
        $hrS['check_wcag22'] = $hrBool('check_wcag22');
        $hrDone = 'Checker settings saved.';
        break;

    case 'save_statement':
        $hrS['org_name'] = $hrRaw('org_name', 120);
        $hrEmail = $hrPost('contact_email');
        if ($hrEmail !== '' && !filter_var($hrEmail, FILTER_VALIDATE_EMAIL)) {
            $hrErrors[] = 'The contact email does not look right, so it was left out of the statement.';
            $hrEmail = '';
        }
        $hrS['contact_email'] = $hrEmail;
        $hrS['contact_phone'] = $hrRaw('contact_phone', 40);
        $hrS['standard'] = $hrPick('standard', array_keys(handrailStandards()), 'wcag21aa');
        $hrS['conformance'] = $hrPick('conformance', array_keys(handrailConformanceOptions()), 'unknown');
        $hrS['limitations'] = $hrRaw('limitations', 5000);
        $hrS['response_days'] = $hrInt('response_days', 0, 60, 5);
        $hrReviewed = $hrPost('reviewed');
        if ($hrReviewed !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hrReviewed)) {
            $hrErrors[] = 'The review date must be a date. It was left blank.';
            $hrReviewed = '';
        }
        $hrS['reviewed'] = $hrReviewed;
        $hrDone = 'Statement saved. The statement page shows the change already.';
        break;

    case 'public_save':
    case 'public_remove':
        $hrOld = trim((string) $hrS['public_path'], '/');
        $hrNew = $hrAction === 'public_save' ? strtolower(trim($hrPost('public_path'), " /")) : '';
        if ($hrAction === 'public_save' && !handrailValidPublicPath($hrNew)) {
            $hrErrors[] = "Use lowercase letters, numbers, dashes and underscores (up to three levels), and not one of UserSpice's own folders.";
            break;
        }
        $hrRoot = $abs_us_root . $us_url_root;
        if ($hrAction === 'public_save') {
            $hrFile = $hrRoot . $hrNew . '/index.php';
            if (is_file($hrFile) && !handrailOwnsFile($hrFile)) {
                $hrErrors[] = $us_url_root . $hrNew . '/ already has its own index.php. Pick another folder.';
                break;
            }
            if (!is_file($hrFile) && (is_dir($hrRoot . $hrNew) || @mkdir($hrRoot . $hrNew, 02775, true))) {
                @file_put_contents($hrFile, handrailPublicWrapper($hrNew));
                @chmod($hrFile, 0664);
            }
        }
        // Retire the previous folder's file if the address changed.
        if ($hrOld !== '' && $hrOld !== $hrNew) {
            handrailRemovePublicPage($hrOld);
        }
        $hrS['public_path'] = $hrNew;
        handrailSaveSettings($hrS);
        if ($hrAction === 'public_remove') {
            $hrDone = "The statement is back at the plugin's own address.";
        } elseif (handrailPublicPath() !== null) {
            $hrDone = 'The statement is now at ' . handrailSiteUrl() . $us_url_root . $hrNew . '/';
        } else {
            $hrErrors[] = 'The web server could not create that folder. Create ' . $hrNew . '/index.php yourself with the code shown under Address.';
        }
        break;

    case 'save_support':
        $hrS['credit'] = $hrBool('credit');
        $hrDone = $hrS['credit'] ? 'The credit link is on. Thank you.' : 'The credit link is off.';
        break;

    case 'report_update':
        $hrId = (int) Input::get('report');
        if (handrailGetReport($hrId)) {
            $hrResolve = $hrPost('status');
            handrailSetStatus($hrId, $hrResolve === 'resolved', $hrRaw('notes', 5000));
            $hrDone = $hrResolve === 'resolved' ? 'Report marked resolved.' : 'Report saved.';
            $hrBack .= '&report=' . $hrId;
        }
        break;

    case 'report_delete':
        $hrId = (int) Input::get('report');
        $db->query('DELETE FROM plg_handrail_reports WHERE id = ?', [$hrId]);
        $hrDone = 'Report deleted.';
        break;

    default:
        $hrErrors[] = 'Nothing was saved. Reload the page and try again.';
}

if (in_array($hrAction, ['save_look', 'save_tools', 'save_reports', 'save_check', 'save_statement', 'save_support'], true)) {
    if (!handrailSaveSettings($hrS)) {
        $hrErrors[] = 'The settings could not be saved.';
        $hrDone = '';
    }
}
foreach ($hrErrors as $hrError) {
    usError($hrError);
}
if ($hrDone !== '') {
    usSuccess($hrDone);
}
Redirect::to($hrBack);
