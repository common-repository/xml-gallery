/*
* Deleta item
*/
function deletItens(){
	var ids = "";
	jQuery(".chItem:checked").each(function(i, a){
		ids += jQuery(this).val() + ", ";
	});
	if (confirm("Do you realy want to delete all of these items?")) {jQuery("#ids").attr("value",ids);jQuery("#del").submit();}
}

/*
* Editar item
*/
function editItem(gal_id){
	jQuery("#iten_id").attr("value",gal_id);
	jQuery("#edit").submit();
}


/*
 * Atualiza opções
 */
function xml_gallery_update_options(){
	if( /^[0-9]+$/.test( jQuery("#qtd").val() ) ){
		jQuery("#xml_gallery").submit();
		jQuery(".msg").addClass("updated");
		jQuery(".msg").text("Updated with sucessfully");
		jQuery(".msg").fadeIn("slow");
	}else{
		jQuery(".msg").addClass("error");
		jQuery(".msg").html("Invalid value");
		jQuery(".msg").fadeIn("slow");
	}
}