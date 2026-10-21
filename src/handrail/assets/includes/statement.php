<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// The accessibility statement, built from Handrail > Statement each time it is shown, so
// changing a setting updates the page without editing it.

require_once __DIR__ . '/settings.php';

if (!function_exists('handrailStandards')) {
    function handrailStandards()
    {
        return ['wcag21aa' => 'WCAG 2.1 Level AA', 'wcag22aa' => 'WCAG 2.2 Level AA', 'wcag20aa' => 'WCAG 2.0 Level AA'];
    }

    function handrailConformanceOptions()
    {
        return [
            'full' => 'Fully conforms',
            'partial' => 'Partially conforms',
            'not' => 'Does not conform yet',
            'unknown' => 'Not assessed yet',
        ];
    }

    /** Reviewed more than a year ago. */
    function handrailStatementStale()
    {
        $r = handrailSettings()['reviewed'];
        return $r !== '' && strtotime($r) < time() - 365 * 86400;
    }
}

if (!function_exists('handrailStatementHtml')) {
    function handrailStatementHtml()
    {
        global $settings;
        $s = handrailSettings();
        $org = trim($s['org_name']) !== '' ? $s['org_name'] : (string) ($settings->site_name ?? 'We');
        $standard = handrailStandards()[$s['standard']] ?? 'WCAG 2.1 Level AA';
        $email = filter_var($s['contact_email'], FILTER_VALIDATE_EMAIL) ? $s['contact_email'] : '';
        $phone = trim($s['contact_phone']);
        $e = function ($t) {
            return safeReturn($t);
        };

        switch ($s['conformance']) {
            case 'full':
                $status = 'We believe this website fully conforms to ' . $standard . '. Fully conforms means the content meets the standard without exceptions. ' . $org . ' still welcomes reports of anything that gets in your way.';
                break;
            case 'not':
                $status = 'This website does not conform to ' . $standard . ' yet. We are working on it.';
                break;
            case 'unknown':
                $status = 'We have not yet completed an assessment of this website against ' . $standard . '.';
                break;
            default:
                $status = 'This website partially conforms to ' . $standard . '. Partially conforms means that some parts of the content do not fully meet the standard.';
        }
        $limits = trim($s['limitations']);

        ob_start(); ?>
<div class="handrail-statement">
  <p><?= $e($org . ' wants everyone to be able to use this website, including people who use screen readers, keyboards, magnification or other assistive technology.') ?></p>

  <h2>How accessible this website is</h2>
  <p><?= $e('The Web Content Accessibility Guidelines (WCAG) explain how to make web content usable for people with disabilities. Our target is ' . $standard . '.') ?></p>
  <p><?= $e($status) ?><?php if ($limits !== '' && $s['conformance'] !== 'full') { ?> The problems we know about are listed below.<?php } ?></p>

  <?php if ($limits !== '') { ?>
  <h2>Known problems</h2>
  <?php foreach (preg_split('/\R{2,}/', $limits) as $para) { ?>
  <p><?= nl2br($e($para)) ?></p>
  <?php } ?>
  <?php } ?>

  <h2>Adjusting this website</h2>
  <p>Use the Accessibility button on any page to change how this website looks and reads for you. Your choices are saved in your own browser. They are sent to us only if you send a problem report, so we can see the page the way you did.</p>

  <h2>Tell us about a problem</h2>
  <p>If something on this website is hard to use, or you need information in a different format, please tell us.</p>
  <ul>
    <?php if (handrailCanReport()) { ?>
    <li><a href="#handrail-report" data-handrail-open="report">Report a problem with this website</a>
      <noscript>(the report form needs JavaScript; please use the email or phone below)</noscript></li>
    <?php } ?>
    <?php if ($email !== '') { ?>
    <li>Email: <a href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a></li>
    <?php } ?>
    <?php if ($phone !== '') { ?>
    <li>Phone: <a href="tel:<?= $e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= $e($phone) ?></a></li>
    <?php } ?>
  </ul>
  <?php if ((int) $s['response_days'] > 0) { ?>
  <p><?= $e('We aim to reply within ' . (int) $s['response_days'] . ' business day' . ((int) $s['response_days'] === 1 ? '' : 's') . '.') ?></p>
  <?php } ?>

  <?php if ($s['reviewed'] !== '' && strtotime($s['reviewed'])) { ?>
  <p class="handrail-statement-reviewed"><?= $e('This statement was last reviewed on ' . date('F j, Y', strtotime($s['reviewed'])) . '.') ?></p>
  <?php } ?>
</div>
<?php
        return (string) ob_get_clean();
    }
}
