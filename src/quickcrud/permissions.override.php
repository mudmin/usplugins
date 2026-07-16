<?php
/**
 * QuickCRUD permission override (template)
 *
 * By default QuickCRUD requires permission level 2 (Admin) everywhere:
 * the database editor, quickCrud() tables, and the AJAX parser.
 *
 * To change who can use QuickCRUD, rename this file to permissions.php
 * (in this same folder) and adjust the hasPerm() call below. Once
 * permissions.php exists, it replaces the built-in level 2 check in all
 * three places. Renaming (rather than editing this file directly) keeps
 * your customization safe when the plugin is updated.
 *
 * This file must return true (allowed) or false (denied). $user is
 * already available and guaranteed to be logged in when this runs.
 */
return hasPerm([2, 3], $user->data()->id);
