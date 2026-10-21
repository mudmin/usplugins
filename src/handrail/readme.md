# Handrail for UserSpice

An accessibility panel for your visitors. They can change how the site looks and reads, and report a barrier. Admins can check the page they're on and see who can fix each problem: the page itself, the template, a plugin, your `usersc/` code, or UserSpice.

Handrail is not an overlay. It never changes the page on its own and never claims the site is compliant. These tools make a site easier to use; they are not a certification or guarantee of accessibility.

Install/activate **Handrail** in the UserSpice Plugin Manager, then open **Handrail** to configure it. A floating Accessibility button appears on every page except the admin dashboard.

## Everything is free

Every feature is free on UserSpice, including the ones that are paid on WordPress: read aloud, the reading guide and mask, the dyslexia font, color filters, large cursors, your own quick settings, branding, and all four panel styles (settings sheet, tile grid, floating card, reading dock).

The panel shows a small "Accessibility tools by Handrail" link. If Handrail helps you, please [donate](https://makewpcompliant.com/donate/?for=handrail-userspice). After that you're welcome to turn the link off under **Credit and support**. It works on trust; there's no key.

## Putting the button somewhere else

Any link to the statement page ending in `#handrail` opens the panel, and without JavaScript it still goes to the statement. Add `-report` or `-check` to open those tabs.

```php
<?php if (function_exists('handrailLink')) { handrailLink('Accessibility'); } ?>
<?php if (function_exists('handrailLink')) { handrailLink('Report a problem', 'btn btn-link', 'report'); } ?>
```

In UltraMenu, add a menu item, set its type to Snippet and pick `usersc/plugins/handrail/menu_hooks/handrail.php`.

## Accessibility statement

Handrail builds the statement from **Statement** each time it's shown. It lives at `usersc/plugins/handrail/statement.php`, or at a shorter address such as `/accessibility/` (Handrail writes a two-line `index.php` there). To show it inside your own page:

```php
<?php if (function_exists('handrailStatement')) { echo handrailStatement(); } ?>
```

## Reports

Off by default. Turn them on for logged-in users or everyone. Each report keeps the page, what the visitor chose and wrote, an optional email, their screen size and the adjustments they had on. Reports arrive under **Reports**, and each one can be emailed to you as it arrives.

Spam protection with no setup: a CSRF token, a honeypot, a minimum time on the form, 5 reports per 10 minutes per address, and a duplicate check. You can add Turnstile, reCAPTCHA or hCaptcha for visitors who aren't logged in, with your own keys or the keys already saved in the Cloudflare Turnstile or reCAPTCHA plugin. The captcha script loads only when someone opens the report form.

## Checker

Admins (or the permission levels you choose under **Checker**) get a **Check this page** tab that runs [axe-core](https://github.com/dequelabs/axe-core) against WCAG 2.1 AA (and 2.2 if you like). Automated checks find about a third of accessibility problems, so also try your pages with a keyboard and a screen reader.

## Privacy and assets

Visitors' adjustments are stored only in their own browser (`localStorage`). Nothing loads from other sites unless you turn on a captcha. Visitors with nothing saved get a small inline script and `button.js`; the panel loads when someone opens it. Scripts carry the UserSpice CSP nonce.

Deactivating keeps settings and reports. Deleting the plugin removes its tables (`plg_handrail_settings`, `plg_handrail_reports`, `plg_handrail_limits`) and the statement folder's `index.php` if Handrail wrote it.

Bundled: axe-core (MPL-2.0), Atkinson Hyperlegible and OpenDyslexic (SIL OFL 1.1). Their licenses are in `core/vendor/` and `pro/vendor/`.
