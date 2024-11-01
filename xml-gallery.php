<?php
/* 
Plugin Name: XML Gallery
Plugin URI: http://brunotnteixeira.wordpress.com/
Version: 1.0
Author: Bruno Neves
Description: This plugin display a xml list.
*/

register_activation_hook(__FILE__,'xml_gallery_install');

define("XML_GALLERY_PATH",ABSPATH."wp-content/xml-gallery/");
define("XML_GALLERY_PATH_THEME",WP_CONTENT_URL."/xml-gallery/gallery.xml");

/*
 * Plugin Activivation [Creating table..]
 */
$xml_gallery_db_version = "1.0"; //Saving version for future actualizations

function xml_gallery_install() {
   global $wpdb;
   global $xml_gallery_db_version;
	
	//Define o nome da tabela
	$table_name = $wpdb->prefix . "xml_gallery";
	
	//Verifica se já não existe uma tabela com o mesmo nome 
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		
		//Create the table [MySQL]
		$sql = "CREATE TABLE " . $table_name . " (
		iten_id INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
		iten_title VARCHAR (255) NOT NULL,
		iten_link VARCHAR (255) NOT NULL,
		iten_text TEXT NOT NULL,
		iten_file VARCHAR (255),
		PRIMARY KEY(iten_id),
		INDEX(iten_id)
		);";
		
		//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//Execute the query
		$wpdb->query($sql);
		
		//Registering configurations
		add_option("xml_gallery_table_name", $table_name);
		add_option("xml_gallery_db_version", $xml_gallery_db_version);
		add_option("xml_gallery_qtd", "3");
		
	}else{
		
		$wpdb->query("DROP $table_name");
		
		//Create the table [MySQL]
		$sql = "CREATE TABLE " . $table_name . " (
		iten_id INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
		iten_title VARCHAR (255) NOT NULL,
		iten_link VARCHAR (255) NOT NULL,
		iten_text TEXT NOT NULL,
		iten_file VARCHAR (255),
		PRIMARY KEY(iten_id),
		INDEX(iten_id)
		);";
		
		//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//Execute the query
		$wpdb->query($sql);
		
		//updating configurations
		if(get_option("xml_gallery_table_name")) update_option("xml_gallery_table_name", $table_name);
		else add_option("xml_gallery_table_name", $table_name);
		
		if(get_option("xml_gallery_db_version")) update_option("xml_gallery_db_version", $xml_gallery_db_version);
		else add_option("xml_gallery_db_version", $xml_gallery_db_version);
		
		if(get_option("xml_gallery_qtd")) update_option("xml_gallery_qtd", "3");
		else  add_option("xml_gallery_qtd", "3");
	}
	
	$path = ABSPATH."wp-content/xml-gallery/";
	
	if( !is_dir( XML_GALLERY_PATH ) ) mkdir($path, 0777);

}

/*********************************************************************************************************************************/

/*
 * Adiciona o menu no painel do WP
 */
function xml_gallery_menu() {
		
//Adicionando menu no admin
add_menu_page('Gallery', 'Gallery', 6, __FILE__ , 'galeria_cad');
	 
	 //adicionando sub-menu
	 add_submenu_page(__FILE__, 'Insert New Iten', 'Insert New Iten', 10, __FILE__, 'galeria_cad');
	 
	//adicionando sub-menu
	add_submenu_page(__FILE__, 'List items', 'List items', 6, 'Listar items', 'xml_gallery_list');
	
	//Adicionando sub-menu
	add_submenu_page(__FILE__, 'Options', 'Options', 6, 'Options', 'xml_gallery_opcoes');
     
}
add_action('admin_menu', 'xml_gallery_menu'); 


/*********************************************************************************************************************************/

/*********************************************************************************************************************************/

## Folha de estilos
function add_xml_gallery_css(){
	//Registering admin style
		wp_register_style("add_xml_gallery_css", WP_PLUGIN_URL . "/xml-gallery/xml-gallery-style.css");
	//Enqueue style
		wp_enqueue_style('add_xml_gallery_css');
}
add_action('admin_print_styles', 'add_xml_gallery_css');

## Scripts
function add_xml_gallery_script(){
	//Registering admin style
		wp_register_script("add_xml_gallery_script", WP_PLUGIN_URL . "/xml-gallery/xml-gallery-script.js");
	//Enqueue style
		wp_enqueue_script('add_xml_gallery_script');
}
add_action('admin_print_scripts', 'add_xml_gallery_script');

/*********************************************************************************************************************************/



function galeria_cad(){


if ( $_POST["add"] == "new" && !$_FILES["file"] ) echo '<div class="error">Is necessary give the file</div>';
else if ( $_POST["add"] == "new" && !$_POST["title"] ) echo '<div class="error">Is necessary give the title</div>';
else if ( $_POST["add"] == "new" && !$_POST["text"] ) echo '<div class="error">Is necessary give the description</div>';
else if ( $_FILES["file"] && $_POST["title"] && $_POST["text"] ) xml_gallery_item($_FILES["file"], $_POST["title"], $_POST["text"], $_POST["link"]);
?>
<div class="wrap">
	<h2>Insert new item</h2>
	
	<p>
		<form id="xml_gallery" method="post" enctype="multipart/form-data">
			<input type="hidden" name="add" value="new" />
			
			<p>
			<label for="file">File</label>
			<input type="file" name="file" id="file" />
			</p>
			
			<br clear="all" />
			
			<p>
			<label for="title">Title</label>
			<input type="text" name="title" id="title" />
			</p>
			
			<br clear="all" />
			
            <p>
			<label for="link">Link</label>
			<input type="text" name="link" id="link" />
			</p>
			
			<br clear="all" />
			
            
			<p>
			<label for="text">Description</label>
			<textarea name="text" id="text" rows="9" cols="39"></textarea>
			</p>
			
			<p><a class="button add-new-h2" href="#" onclick="jQuery('#xml_gallery').submit()">Insert</a></p>
		</form>
	</p>
	
</div>
<?php
}

function write_xml(){
	global $wpdb;
	
	$path = ABSPATH."wp-content/xml-gallery/";
	if( !is_dir( XML_GALLERY_PATH ) ) mkdir(XML_GALLERY_PATH, 0777);
	
	$registros = $wpdb->get_results( "SELECT * FROM " . get_option("xml_gallery_table_name") . " ORDER BY iten_id desc LIMIT " . get_option("xml_gallery_qtd") );
	
	$xmlDoc = new DOMDocument("1.0", "utf-8");
	$xmlDoc -> formatOutput = true;
	
	##root
	$root = $xmlDoc -> createElement("root");
	$root = $xmlDoc -> appendChild($root);
			
	foreach($registros as $item){
		
		##item
		$iten = $xmlDoc -> createElement("iten");
		$iten = $root -> appendChild($iten);
		
			##file
			$file = $xmlDoc -> createElement("file", $item->iten_file);
			$file = $iten -> appendChild($file);
			##/file
			
			##title
			$title = $xmlDoc -> createElement("title", $item->iten_title);
			$title = $iten -> appendChild($title);
			##/title
			
			##text
			$text= $xmlDoc -> createElement("text", $item->iten_text);
			$text = $iten -> appendChild($text);
			##/text
			
			##descricao
			$link = $xmlDoc -> createElement("link", $item->iten_link);
			$link = $iten -> appendChild($link);
			##/descricao
			
		##/item
		
	}
		
		
	##/root
	
	$xmlDoc -> save(XML_GALLERY_PATH."gallery.xml");
}

function xml_gallery_item($file, $tit, $desc, $link, $type = 'add', $id = ''){
	global $wpdb;
	
	$path = ABSPATH."wp-content/xml-gallery/";
	if( !is_dir( XML_GALLERY_PATH ) ) mkdir(XML_GALLERY_PATH, 0777);
	
	if( $type == "add" ){
		
		if($file[tmp_name]){
			$ext = explode(".",$file[name]);
			$nameFile = $ext[0].".".$ext[1];
			move_uploaded_file($file[tmp_name],XML_GALLERY_PATH.$nameFile);
		}
		
		$results = $wpdb->query("INSERT INTO " . get_option("xml_gallery_table_name") . " (iten_title, iten_text, iten_file, iten_link) values('$tit','$desc','/wp-content/xml-gallery/".$nameFile . "', '$link')");
		
		if( $results ) {echo '<div class="updated errorupdated error">Saved with Sucess.</div>';write_xml();}
		else echo '<div class="error">Fail while trying recording.</div>';
		
	}else if( $type == "edit" ){
		$reg = $wpdb->get_results( "SELECT * FROM " . get_option("xml_gallery_table_name") . " WHERE iten_id = $id" );
		$haveImg = "";
		
		foreach($reg as $item){
		
			if( $file ){
				@unlink( ABSPATH . $item->iten_file );				
				if($file[tmp_name]){
					$ext = explode(".",$file[name]);
					$nameFile = $ext[0].".".$ext[1];
					move_uploaded_file($file[tmp_name],XML_GALLERY_PATH.$nameFile);
					$haveImg = " , iten_file = '/wp-content/xml-gallery/".$nameFile."' ";
				}
				
			}
			
			$qUpdate = "UPDATE " . get_option("xml_gallery_table_name") . " SET iten_title = '$tit', iten_link = '$link', iten_text = '$desc' $haveImg WHERE iten_id = $id";
			
			//echo $qUpdate;
			
			$results = $wpdb->query( $qUpdate );
			
			if( $results ) {echo '<div class="updated errorupdated error">Edited with sucess.</div>';write_xml();}
			else echo '<div class="error">Fail while trying editing.</div>';
		}
	}
}

/*********************************************** PAGINAS ********************************************************************/

function xml_gallery_list(){
	global $wpdb;
	$qIds = "";
	$sucess = "";
	$err = "";
	
	if($_POST["ids"]) del_item($_POST["ids"]);
	if($_POST["iten_id"]) edit_item($_POST["iten_id"]);
	if($_POST["edit"]){if($_FILES["file"]["name"]) $path_to_file = $_FILES["file"]; xml_gallery_item($path_to_file, $_POST["title"], $_POST["text"], $_POST["link"], "edit", $_POST["edit"]);}
	
	
	$galery = $wpdb->get_results( "SELECT * FROM " . get_option("xml_gallery_table_name") . " ORDER BY iten_id desc");
	
	?>
	
	<div class="notice"></div>
		
	<h2>Galery  <a class="button add-new-h2" href="#" onclick="deletItens()">Delete Items</a> </h2>
	<form id="del" method="post"><input type="hidden" id="ids" name="ids" /></form>
	<form id="edit" method="post"><input type="hidden" id="iten_id" name="iten_id" /></form>
	<br/>
	
	<table cellspacing="0" class="widefat page fixed">
		<thead>
		    <tr>
		        <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox" class="checkAll" /></th>
		        <th style="" class="manage-column column-title" id="title" scope="col">Title</th>
		        <th style="" class="manage-column column-date" id="date" scope="col">Description</th>
		    </tr>
		</thead>
		
		<tfoot>
		    <tr>
		        <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="checkAll" /></th>
		        <th style="" class="manage-column column-title" id="title" scope="col">Title</th>
		        <th style="" class="manage-column column-date" id="date" scope="col">Description</th>
		    </tr>
		</tfoot>
		<tbody>
	<?php
	
	foreach ($galery as $iGaley){
		if($iGaley){
		?>
			<tr class="alternate iedit" id="page-2">
	        <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $iGaley->iten_id ?>" class="chItem"/></th>
	        <td class="post-title page-title column-title">
	            <strong><a title="Edit &ldquo;<?php echo $iGaley->iten_title ?>&rdquo;" href="#" class="row-title"><?php echo $iGaley->iten_title ?></a></strong>
	            <div class="row-actions"><span class="edit"><a title="Edit item" href="#" onclick="editItem('<?php echo $iGaley->iten_id ?>')">Edit</a> </span></div>
	        </td>
	        <td class="date column-date"><?php echo substr($iGaley->iten_text, 0, 30); if(strlen($iGaley->iten_text) > 30) echo "..."; ?></td>
		</tr>
		<?php
		}else{
			?>
			<tr class="alternate iedit" id="page-2">
	        <th class="check-column" scope="row"></th>
	        <td class="post-title page-title column-title">
	            <strong><a title="Edit" href="#" class="row-title">Nothing founded...</a></strong>
	        </td>
	        <td class="date column-date"></td>
			<?php
		}
	}
	?>
		</tbody>
	</table>
	<?php
}

/*
* Exclui item
*/

function del_item($post_ids){
	global $wpdb;
	
	$ids = substr($post_ids, 0, strlen($post_ids) - 2);
	$ids = split(", ", $ids);
	
	for($i=0; $i<=count($ids)-1; $i++){
		if ($i != count($ids)-1) $qIds .= $ids[$i] . ",";
		else $qIds .= $ids[$i];
	}
	
	$imgs = $wpdb->get_results( "SELECT * FROM " . get_option("xml_gallery_table_name") . " WHERE iten_id in($qIds)" );
	
	foreach($imgs as $img){
		$qDeleta = "DELETE FROM " . get_option("xml_gallery_table_name") . " WHERE iten_id = " . $img->iten_id;
		if( @unlink( ABSPATH . $img->iten_arquivo ) )
			if( $wpdb->query( $qDeleta ) ) {$sucess .= "Items deleted successfully!";write_xml();}
			else $err .= " - Fail while trying deleting: <b>".$img->iten_title."</b>, please try again.";
		else $err .= "Fail while trying deleting the file: <b>".$img->iten_file."</b>, please try again.";
	}
	if( $sucess ) $sucessMsg = "<div class=\"updated error\">$sucess</div>";
	if( $err ) $errMsg = "<div class=\"error\">$err</div>";
	
	if( $sucessMsg ) echo $sucessMsg;
	if( $errMsg ) echo $errMsg;
	
}

function edit_item($iten_id){
	global $wpdb;
	
	$reg = $wpdb->get_results( "SELECT * FROM " . get_option("xml_gallery_table_name") . " WHERE iten_id = $iten_id" );
	
	foreach($reg as $item){
?>
<div class="wrap">
	<h2>Editar item da galeria</h2>
	
	<p>
		<form id="xml_gallery" method="post" enctype="multipart/form-data">
			<input type="hidden" name="edit" value="<?php echo $iten_id ?>" />
			
			<p>
			<label for="titulo">File</label>
			<input type="file" name="file" id="file" />
			<small>If you don't want to modify this, please only ignore it.</small>
			</p>
			
			<br clear="all" />
			
			<p>
			<label for="tiltle">Title</label>
			<input type="text" name="title" id="title" value="<?php echo $item->iten_title ?>" />
			</p>
			
			<br clear="all" />
			
            <p>
			<label for="link">Link</label>
			<input type="text" name="link" id="link" value="<?php echo $item->iten_link ?>" />
			</p>
			
			<br clear="all" />
			
            
			<p>
			<label for="text">Description</label>
			<textarea name="text" id="text" rows="9" cols="39"><?php echo $item->iten_text ?></textarea>
			</p>
			
			<p><a class="button add-new-h2" href="#" onclick="jQuery('#xml_gallery').submit()">Save</a></p>
		</form>
	</p>
	
</div>
<?php
	}
exit;
}

/*
 * Página de opções
 */
function xml_gallery_opcoes(){
	if($_POST["qtd"]) {update_option("xml_gallery_qtd", $_POST["qtd"]);write_xml();}
?>
<div class="wrap">
	
	<div class="msg" style="display:none;"></div>
	
	<h2>Edit XML Galery options</h2>
	
	<form id="xml_gallery" method="post">
		<p>
		<label for="qtd" class="labelMaior">Quantity of highlights</label>
		<input type="text" name="qtd" id="qtd" value="<?php echo get_option("xml_gallery_qtd");?>" class="txtPeq" />
		</p>
		
		<br clear="all" />
		
		<p><a class="button add-new-h2" href="#" onclick="xml_gallery_update_options()">Save</a></p>
	</form>
	
</div>

<?php
	
}

/*
* Theme function to show the list image
*/
function xml_gallery_theme($before="<div",$after="</div>",$cssClass='iten'){
	$xmlDoc = simplexml_load_file(XML_GALLERY_PATH_THEME);
	
	if($before == "<div") $before .= " class=\"" . $cssClass . "\" >";
	
	foreach($xmlDoc->iten as $iten){
			echo $before;
				echo	"<a href=\"$iten->link\" alt=\"$iten->title\"><img src=\"".get_option('home')."$iten->file\" width=\"79\" height=\"79\" alt=\"$iten->title\" /></a> \n
						<p><a href=\"$iten->link\" alt=\"$iten->title\">$iten->text</p></a>";
			echo $after;
	}
}

?>