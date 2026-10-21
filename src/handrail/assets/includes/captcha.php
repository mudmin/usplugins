<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Optional captcha for anonymous reports. Keys are typed in, or borrowed from the UserSpice
// Turnstile or reCAPTCHA plugin. Handrail renders and verifies the captcha itself; it only
// reads the other plugin's keys, never calls its code.

require_once __DIR__ . '/settings.php';

if (!function_exists('handrailCaptchaProviders')) {
    function handrailCaptchaProviders()
    {
        return [
            'none' => 'None (honeypot, timing and rate limit only)',
            'turnstile' => 'Cloudflare Turnstile (recommended)',
            'recaptcha_v3' => 'Google reCAPTCHA v3 (invisible score)',
            'recaptcha_v2' => 'Google reCAPTCHA v2 checkbox',
            'hcaptcha' => 'hCaptcha',
        ];
    }
}

if (!function_exists('handrailCaptchaDetect')) {
    /**
     * Key sets other plugins on this site already store.
     * Each: source, provider, label, sitekey, secret.
     */
    function handrailCaptchaDetect()
    {
        global $db;
        $found = [];
        try {
            if (pluginActive('turnstile', true)) {
                $q = $db->query('SELECT site_key, secret_key FROM plg_turnstile_settings WHERE id = 1');
                if (!$db->error() && $q->count()) {
                    $r = $q->first();
                    $found[] = handrailCaptchaRow('us_turnstile', 'turnstile', 'Cloudflare Turnstile plugin', $r->site_key, $r->secret_key);
                }
            }
            if (pluginActive('recaptcha', true)) {
                $q = $db->query('SELECT * FROM settings WHERE id = 1');
                $r = (!$db->error() && $q->count()) ? $q->first() : null;
                // v2 keys work for the checkbox only when the plugin is set to the checkbox (type 1).
                if ($r && isset($r->recap_public, $r->recap_private)) {
                    $version = (int) ($r->recap_version ?? 2);
                    if ($version === 3) {
                        $found[] = handrailCaptchaRow('us_recaptcha', 'recaptcha_v3', 'reCAPTCHA plugin', $r->recap_public, $r->recap_private);
                    } elseif ((int) ($r->recap_type ?? 1) === 1) {
                        $found[] = handrailCaptchaRow('us_recaptcha', 'recaptcha_v2', 'reCAPTCHA plugin', $r->recap_public, $r->recap_private);
                    }
                }
            }
        } catch (Throwable $e) {
            // A plugin whose tables are missing simply offers nothing.
        }
        return array_values(array_filter($found));
    }

    function handrailCaptchaRow($source, $provider, $label, $site, $secret)
    {
        $site = trim((string) $site);
        $secret = trim((string) $secret);
        // The reCAPTCHA plugin installs with placeholder keys.
        if ($site === '' || $secret === '' || stripos($site, 'Your-') === 0) {
            return null;
        }
        return ['source' => $source, 'provider' => $provider, 'label' => $label, 'sitekey' => $site, 'secret' => $secret];
    }
}

if (!function_exists('handrailCaptchaActive')) {
    /** The keys in use, or null for no captcha (including when a borrowed source has gone away). */
    function handrailCaptchaActive()
    {
        $s = handrailSettings();
        $provider = $s['captcha_provider'];
        if ($provider === 'none' || !isset(handrailCaptchaProviders()[$provider])) {
            return null;
        }
        if ($s['captcha_source'] === 'manual') {
            if ($s['captcha_sitekey'] === '' || $s['captcha_secret'] === '') {
                return null;
            }
            return ['source' => 'manual', 'provider' => $provider, 'sitekey' => $s['captcha_sitekey'], 'secret' => $s['captcha_secret']];
        }
        foreach (handrailCaptchaDetect() as $row) {
            if ($row['source'] === $s['captcha_source'] && $row['provider'] === $provider) {
                return $row;
            }
        }
        return null;
    }

    /** A borrowed key source was chosen but isn't there any more. */
    function handrailCaptchaMissing()
    {
        $s = handrailSettings();
        return $s['captcha_provider'] !== 'none' && $s['captcha_source'] !== 'manual' && !handrailCaptchaActive();
    }
}

if (!function_exists('handrailCaptchaVerify')) {
    /** True, or an error message for the visitor. */
    function handrailCaptchaVerify($keys, $token)
    {
        if (!is_string($token) || $token === '' || strlen($token) > 4096) {
            return 'Please complete the check before sending.';
        }
        if (!function_exists('curl_init')) {
            return 'The spam check could not be reached. Please try again.';
        }
        $endpoints = [
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'recaptcha_v2' => 'https://www.google.com/recaptcha/api/siteverify',
            'recaptcha_v3' => 'https://www.google.com/recaptcha/api/siteverify',
            'hcaptcha' => 'https://api.hcaptcha.com/siteverify',
        ];
        $body = ['secret' => $keys['secret'], 'response' => $token];
        if ($keys['provider'] === 'hcaptcha') {
            $body['sitekey'] = $keys['sitekey'];
        }
        $ch = curl_init($endpoints[$keys['provider']]);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!is_string($raw) || $status !== 200) {
            return 'The spam check could not be reached. Please try again.';
        }
        $data = json_decode($raw, true);
        $fail = 'The spam check failed. Please try again.';
        if (!is_array($data) || empty($data['success'])) {
            return $fail;
        }
        $host = strtolower((string) parse_url(handrailSiteUrl(), PHP_URL_HOST));
        // Provider test keys always pass and report a placeholder hostname.
        $testing = !empty($data['metadata']['result_with_testing_key']);
        if (!$testing && !empty($data['hostname']) && strtolower((string) $data['hostname']) !== $host) {
            return $fail;
        }
        if (in_array($keys['provider'], ['turnstile', 'recaptcha_v3'], true) && isset($data['action']) && $data['action'] !== 'handrail_report') {
            return $fail;
        }
        if ($keys['provider'] === 'recaptcha_v3') {
            $min = (float) handrailSettings()['captcha_v3_score'];
            if (!isset($data['score']) || (float) $data['score'] < $min) {
                return $fail;
            }
        }
        return true;
    }
}
