<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$plg_settings = $db->query("SELECT * FROM plg_cms_settings")->first();
if(!empty($_POST['parser'])){
  $fields = array(
    'parser'=>Input::get('parser'),
  );
  $db->update('plg_cms_settings',1,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=cms&err=Settings+Saved');
}
?>

<div class="row">
  <div class="col-12">
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">Default Parser File Location</label>
        <p>The parser file is a very simple file that magicically merges your content/widgets/layouts into
           full page of content. By default it is in <strong>usersc/plugins/cms/content.php</strong> but you can duplicate it as
           many times as you want and rename it whatever you want.  For instance, you may want to call it
          <strong>blog.php</strong> and put it in your root. That's fine. </p>
        <p>If we update the parser file to add features, we will put a notification out so you
        can roll those changes into your duplicate parsers.</p>
        <input type="text" class="form-control" name="parser" value="<?=$plg_settings->parser?>" required>
        <input type="submit" name="submit" value="Save">
      </div>
      <div class="form-group">
        <p>Don't like our HTML WYSIWYG editor?  You can create a <strong>usersc/includes/cmseditor.php</strong>
        and setup your own! We even provide an example for another editor in <strong>usersc/plugins/cms/assets/cmseditor-alt.php</strong>.</p>
        <div class="alert alert-light border py-2 px-3 small text-muted" role="note">
          <i class="fa fa-info-circle mr-1"></i>
          <strong>CSP note:</strong> the default Summernote editor loads from <code>https://cdnjs.cloudflare.com</code>, and the example CKEditor editor (<code>cmseditor-alt.php</code>) from <code>https://cdn.ckeditor.com</code>. If your site sends a <em>Content-Security-Policy</em> header, add those origins to <code>script-src</code> (and <code>style-src</code>) or the editor will not load.
        </div>
      </div>
    </form>
  </div>
</div>
