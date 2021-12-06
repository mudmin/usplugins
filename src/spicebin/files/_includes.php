<?php
// NOTE: If you want to customize these includes, you can just duplicate this file and rename to _custom_includes.php and your file will be loaded instead of ours. This will prevent updates from breaking your customizations.
global $paste,$pset;
?>
<link rel="stylesheet" href="<?=$us_url_root?>usersc/plugins/spicebin/assets/lib/codemirror.css">
<link rel="stylesheet" href="<?=$us_url_root?>usersc/plugins/spicebin/assets/theme/<?=$pset->theme?>.css">
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/lib/codemirror.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/htmlmixed/htmlmixed.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/javascript/javascript.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/css/css.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/xml/xml.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/clike/clike.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/addon/edit/matchbrackets.js"></script>
<?php
$skip = ['clike','xml','css','htmlmixed','javascript'];
if(isset($paste->lang) && !in_array($paste->lang,$skip) && $paste->lang != ""){ ?>
  <script src="<?=$us_url_root?>usersc/plugins/spicebin/assets/mode/<?=$paste->lang?>/<?=$paste->lang?>.js"></script>
<?php } ?>

<style>
.CodeMirror {
  min-width:100%;
  /* might wanna lose the next line */
  height:auto;
}
.CodeMirror-wrap pre.CodeMirror-line, .CodeMirror-wrap pre.CodeMirror-line-like {
  word-break: normal;
}
</style>
