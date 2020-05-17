<?php if(count(get_included_files()) ==1) die();
//This is an example of a usersc/includes/cmseditor.php file that could be created
//to use an alternate editor
?>

<script src='https://cdn.tinymce.com/4/tinymce.min.js'></script>
<script type="text/javascript">
$(document).ready(function(){
//Tiny MCE4
tinymce.init({
  selector: '#editor',
  plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap emoticons',
  imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: "30s",
  autosave_prefix: "{path}{query}-{id}-",
  autosave_restore_when_empty: false,
  autosave_retention: "2m",
  image_advtab: true,
  content_css: '//www.tiny.cloud/css/codepen.min.css',
  link_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  height: 400,
  images_upload_handler: function (blobInfo, success, failure) {
    var xhr, formData;

    xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.open('POST', "<?=$us_url_root?>usersc/plugins/cms/imguploader.php");

    xhr.onload = function() {
      var json;

      if (xhr.status != 200) {
        failure('HTTP Error: ' + xhr.status);
        return;
      }

      json = JSON.parse(xhr.responseText);

      if (!json || typeof json.location != 'string') {
        failure('Invalid JSON: ' + xhr.responseText);
        return;
      }

      success(json.location);
    };

    formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());

    xhr.send(formData);
  },
  file_browser_callback: function(field_name, url, type, win) {
    win.document.getElementById(field_name).value = 'my browser value';
  },
  templates: [
    { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 400,
  image_caption: true,
  noneditable_noneditable_class: "mceNonEditable",
  toolbar_mode: 'sliding',
  contextmenu: "link image imagetools table",
 });

});
</script>
