  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$method = Input::get('method');
if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
}
 $token = Token::generate();
 ?>

<div class="content mt-3">
<?php
$plg_settings = $db->query("SELECT * FROM plg_cms_settings")->first();
include 'assets/menu.php';
if(file_exists($abs_us_root.$us_url_root."usersc/plugins/cms/assets/".$method.".php")){
  include $abs_us_root.$us_url_root."usersc/plugins/cms/assets/".$method.".php";
}else{ ?>
  <h3>Basic Documentation</h3>
  <h4><a href="https://youtu.be/E0ud3miS2X4">Click Here for a Video Tour</a></h4>
  <p>This plugin is great for creating content that will be stored in the database so you
  do not need to create a new php file for each page (like Wordpress/Joomla).  While it's
  not meant to be a replacement for those systems, it does have one big trick up its sleeve--
  UserSpice goodness!</p>

  <p>In addition to typing and displaying content, you can have widgets.  Widgets can
    be more HTML content or they can be actual php files that are loaded on the page. This
    allows you to mix your content with all the UserSpice functionality.  Widget files are
    stored in <strong>usersc/plugins/cms/widgets</strong>.
  </p>

  <p>The real power of widgets comes from the layout manager.  You can create as many HTML
  layouts and use simple shortcodes to determine where your widgets and content are supposed
  to be on the page.  Documentation for this is found on that page.</p>

  <p>Content can be organized into categories.  This is where your permissions are set. Each
    category determines who can view that content.  You can have as many categories and
    subcategories as you want.</p>

  <p>Finally we have the parser file.  The parser file can be copied anywhere in your project
  and is what displays you content. So you can copy <strong>usersc/plugins/cms/content.php</strong>
  to <strong>users/blog.php</strong> for example and then link to blog content with either
  blog.php?c=123 where 123 is the content id OR you can do blog.php?c=this-is-the-article-name
  using the article slug.
</p>

  <p><strong>Issues:</strong> I'm still tinkering wtih the WYSIWYG editors. Ideally you will be
    able to choose from several editors to meet your needs.  I'm also tinkering with adding/editing
    images and media.  Right now in TinyMCE you an upload images but it's clunky.  I think this
    can be a VERY POWERFUL plugin and I would love to see the community help develop it.
  </p>
  <p>
    Finally, due to the powerful nature of storing code in the database, content is designed to be
    created <strong>by master account admins only</strong>. This may change as the plugin matures
    but at this time I only feel comfortable with content being developed by trusted admins.

  </p>
<?php } ?>
