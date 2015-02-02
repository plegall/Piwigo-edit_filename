<?php 
/*
Plugin Name: Edit Filename
Version: auto
Description: Edit original filename of the photo
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=
Author: plg
Author URI: http://le-gall.net/pierrick
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

// Add a prefilter
add_event_handler('loc_begin_admin', 'editfilename_set_prefilter_modify');
function editfilename_set_prefilter_modify()
{
	global $template;
	$template->set_prefilter('picture_modify', 'editfilename_modify');
}

function editfilename_modify($content, &$smarty)
{
  $search = "<legend>{'Properties'|@translate}</legend>";

  // We use the <tr> from the Creation date, and give them a new <tr>
  $replacement = $search.'

    <p>
      <strong>{\'File name\'|@translate}</strong>
      <br>
      <input type="text" class="large" name="filename" value="{$FILENAME|@escape}">.{$FILENAME_EXTENSION}
    </p>';

  return str_replace($search, $replacement, $content);
}

// Change the variables used by the function that changes the template
add_event_handler('loc_end_picture_modify', 'editfilename_add_modify_vars_to_template');
function editfilename_add_modify_vars_to_template()
{
  global $image_file, $template;

  $file_wo_ext = get_filename_wo_extension($image_file);
  $ext = get_extension($image_file);
  
  $template->assign(
    array(
      'FILENAME' => $file_wo_ext,
      'FILENAME_EXTENSION' => $ext,
      )
    );
}

add_event_handler('picture_modify_before_update', 'editfilename_modify_submit');
function editfilename_modify_submit($data)
{
  // get the extension, which cannot be changed
  $query = '
SELECT
    file
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$data['id'].'
;';
  $rows = query2array($query);

  if (count($rows) == 0)
  {
    return $data;
  }
  
  $ext = get_extension($rows[0]['file']);

  // add the property to update
  $data['file'] = $_POST['filename'].'.'.$ext;
  
  return $data;
}