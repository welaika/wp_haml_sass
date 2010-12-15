<?php

/**
 * return an array of the post types that we have set up post connectors for
 * @param return array
 */
function simple_fields_post_connector_attached_types() {
	$post_connectors = (array) get_option("simple_fields_post_connectors");
	$arr_post_types = array();
	foreach ($post_connectors as $one_post_connector) {
		$arr_post_types = array_merge($arr_post_types, (array) $one_post_connector["post_types"]);
	}
	$arr_post_types = array_unique($arr_post_types);
	return $arr_post_types;
}

function simple_fields_get_post_connectors_for_post_type($post_type) {

	$arr_post_connectors = simple_fields_get_post_connectors();
	$arr_found_connectors = array();

	foreach ($arr_post_connectors as $one_connector) {
		if ($one_connector && in_array($post_type, $one_connector["post_types"])) {
			$arr_found_connectors[] = $one_connector;
		}
	}
	return $arr_found_connectors;
}

function simple_fields_get_post_connectors() {
	return (array) get_option("simple_fields_post_connectors");
}

function simple_fields_admin_menu() {
	add_submenu_page( 'options-general.php' , EASY_FIELDS_NAME, EASY_FIELDS_NAME, "administrator", "simple-fields-options", "simple_fields_options");
}

function simple_fields_options() {

	$field_groups = get_option("simple_fields_groups");
	$post_connectors = get_option("simple_fields_post_connectors");

	/*
	$field_groups = get_option("easy_fields_groups");
	$post_connectors = get_option("easy_fields_post_connectors");
	update_option("simple_fields_groups", $field_groups);
	update_option("simple_fields_post_connectors", $post_connectors);
	// */

	// for debug purposes, here we can reset the option
	#$field_groups = array(); update_option("simple_fields_groups", $field_groups);
	#$post_connectors = array(); update_option("simple_fields_post_connectors", $post_connectors);

	// first run? make sure field groups is an array
	if (!$field_groups) {
		$field_groups = array();
	}
	if (!$post_connectors) {
		$post_connectors = array();
	}

	// sort them by name
	function simple_fields_uasort($a, $b) {
		if ($a["name"] == $b["name"]) { return 0; }
		return strcasecmp($a["name"], $b["name"]);
	}
	
	uasort($field_groups, "simple_fields_uasort");
	uasort($post_connectors, "simple_fields_uasort");
	
	// sometimes we get a empty field group on pos zero.. wierd.. can't find the reason for it right now.. :(
	#if ($field_groups[0] && empty($field_groups[0]["name"])) {
	#	unset($field_groups[0]);
	#}

	?>
	<div class="wrap">

		<h2><?php echo EASY_FIELDS_NAME ?></h2>
	
		<!-- <ul class="subsubsub">
			<li><a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=simple-fields-edit-connectors">Post connectors</a> |</li>
			<li><a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=simple-fields-edit-field-groups">Field groups</a></li>
		</ul> -->
		
		<div class="clear"></div>
		
		<?php
		$action = (isset($_GET["action"])) ? $_GET["action"] : null;
		
		/**
		 * save post type defaults
		 */
		if ("edit-post-type-defaults-save" == $action) {
			$post_type = $_POST["post_type"];
			$post_type_connector = $_POST["post_type_connector"];
			$post_type_defaults = (array) get_option("simple_fields_post_type_defaults");
			$post_type_defaults["$post_type"] = $post_type_connector;
			update_option("simple_fields_post_type_defaults", $post_type_defaults);
			$simple_fields_did_save_post_type_defaults = true;
			$action = "";
		}

		/**
		 * edit post type defaults
		 */
		if ("edit-post-type-defaults" == $action) {
			$post_type = $_GET["post-type"];
			global $wp_post_types;
			if (isset($wp_post_types[$post_type])) {
				$selected_post_type = $wp_post_types[$post_type];
				?>
				<h3>Post type <?php echo $post_type ?></h3>
				
				<form action="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-post-type-defaults-save" method="post">
					<table class="form-table">
						<tr>
							<th>Default post connector</th>
							<td>
								<?php
								$arr_post_connectors = simple_fields_get_post_connectors_for_post_type($post_type);
								if ($arr_post_connectors) {
									$selected_post_type_default = simple_fields_get_default_connector_for_post_type($post_type);
									?>
									<select name="post_type_connector">
										<option <?php echo ($selected_post_type_default=="__none__") ? " selected='selected' " : "" ?> value="__none__">No post connector</option>
										<option <?php echo ($selected_post_type_default=="__inherit__") ? " selected='selected' " : "" ?> value="__inherit__">Inherit from parent post</option>
										<?php
										foreach ($arr_post_connectors as $one_post_connector) {
											echo "<option " . (($selected_post_type_default==$one_post_connector["id"]) ? " selected='selected' " : "") . "value='{$one_post_connector["id"]}'>" . $one_post_connector["name"] . "</option>";
										}
										?>
									</select>
									<?php
								} else {
									?><p>There are no post connectors for this post type.</p><?php
								}
								?>
							</td>
						</tr>
					</table>
					<p class="submit">
						<input class="button-primary" type="submit" value="Save Changes" />
						<input type="hidden" name="post_type" value="<?php echo $post_type ?>" />
						or 
						<a href="<?php echo EASY_FIELDS_FILE ?>">cancel</a>
					</p>
				</form>
				<?php
				#d($selected_post_type);
			}
		}

		/**
		 * Delete a field group
		 */
		if ("delete-field-group" == $action) {
			$field_group_id = (int) $_GET["group-id"];
			$field_groups[$field_group_id]["deleted"] = true;
			update_option("simple_fields_groups", $field_groups);
			$simple_fields_did_delete = true;
			$action = "";
		}

		/**
		 * Delete a post connector
		 */
		if ("delete-post-connector" == $action) {
			$post_connector_id = (int) $_GET["connector-id"];
			$post_connectors[$post_connector_id]["deleted"] = 1;
			update_option("simple_fields_post_connectors", $post_connectors);
			$simple_fields_did_delete_post_connector = true;
			$action = "";
		}
		

		/**
		 * save a post connector
		 */
		if ("edit-post-connector-save" == $action) {
			if ($_POST) {
				
				#d($_POST);
				#d($post_connectors);
				
				$connector_id = (int) $_POST["post_connector_id"];
				$post_connectors[$connector_id]["name"] = (string) $_POST["post_connector_name"];
				$post_connectors[$connector_id]["field_groups"] = (array) $_POST["added_fields"];
				$post_connectors[$connector_id]["post_types"] = (array) $_POST["post_types"];

				// a post type can only have one default connector, so make sure only the connector
				// that we are saving now has it; remove it from all others;
				/*
				$post_types_type_default = (array) $_POST["post_types_type_default"];
				foreach ($post_types_type_default as $one_default_post_type) {
					foreach ($post_connectors as $one_post_connector) {
						if (in_array($one_default_post_type, $one_post_connector["post_types_type_default"])) {
							$array_key = array_search($one_default_post_type, $one_post_connector["post_types_type_default"]);
							if ($array_key !== false) {
								unset($post_connectors[$one_post_connector["id"]]["post_types_type_default"][$array_key]);
							}
						}
					}
				}
				$post_connectors[$connector_id]["post_types_type_default"] = $post_types_type_default;
				*/
				
				update_option("simple_fields_post_connectors", $post_connectors);
				$simple_fields_did_save_connector = true;
			}
			#$action = "simple-fields-edit-connectors";
			$action = "";
		}
		
		/**
		 * save a field group
		 * including fields
		 */
		if ("edit-field-group-save" == $action) {
			/*
			Array
			(
			    [field_group_name] => Unnamed field group 59 changed
			    [action] => update
			    [page_options] => field_group_name
			    [field_group_id] => 59
			)
			*/
			if ($_POST) {
				$field_group_id = (int) $_POST["field_group_id"];
				$field_groups[$field_group_id]["name"] = $_POST["field_group_name"];
				$field_groups[$field_group_id]["repeatable"] = (bool) $_POST["field_group_repeatable"];
				$field_groups[$field_group_id]["fields"] = (array) $_POST["field"];
		
				$field_groups[$field_group_id]["type_textarea_options"] = (array) @$_POST["type_textarea_options"];
				$field_groups[$field_group_id]["type_radiobuttons_options"] = (array) @$_POST["type_radiobuttons_options"];
		
				update_option("simple_fields_groups", $field_groups);
	
				$simple_fields_did_save = true;
			}
			#$action = "simple-fields-edit-field-groups";
			$action = "";
					
		}

		
		/**
		 * edit new or existing post connector
		 */
		if ("edit-post-connector" == $action) {
			$connector_id =  (int) $_GET["connector-id"];
			$highest_connector_id = 0;
	
			// if new, save it as unnamed, and then set to edit that
			if ($connector_id == 0) {
				foreach ($post_connectors as $oneConnector) {
					if ($oneConnector["id"]>$highest_connector_id) {
						$highest_connector_id = $oneConnector["id"];
					}
				}
				$highest_connector_id++;
				$connector_id = $highest_connector_id;
				
				$post_connectors[$connector_id] = array(
					"id" => $connector_id,
					"name" => "Unnamed post connector $connector_id",
					"field_groups" => array(),
					"post_types" => array(),
					#"post_types_type_default" = array(),
					"deleted" => false,
				);
				
				update_option("simple_fields_post_connectors", $post_connectors);

			} else {
				// existing post connector
			}

			$post_connector_in_edit = $post_connectors[$connector_id];

			?>
			<h3>Post Connector details</h3>

			<form method="post" action="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-post-connector-save">

				<table class="form-table">
					<tr>
						<th><label>Name</label></th>
						<td><input type="text" id="post_connector_name" name="post_connector_name" class="regular-text" value="<?php echo esc_html($post_connector_in_edit["name"]) ?>" /></td>
					</tr>
					<tr>
						<th>Field Groups</th>
	
						<td>
							<p>
								<select id="simple-fields-post-connector-add-fields">
									<option value="">Add field group...</option>
									<?php
									foreach ($field_groups as $one_field_group) {
										if ($one_field_group["deleted"]) { continue; }
										?><option value='<?php echo $one_field_group["id"] ?>'><?php echo esc_html($one_field_group["name"]) ?></option><?php
									}
									?>
								</select>
							</p>
							<ul id="simple-fields-post-connector-added-fields">
								<?php
								foreach ($post_connector_in_edit["field_groups"] as $one_post_connector_added_field) {
									if ($one_post_connector_added_field["deleted"]) { continue; }
									#d($one_post_connector_added_field);
									?>
									<li>
										<div class='simple-fields-post-connector-addded-fields-handle'></div>
										<div class='simple-fields-post-connector-addded-fields-field-name'><?php echo $one_post_connector_added_field["name"] ?></div>
										<input type='hidden' name='added_fields[<?php echo $one_post_connector_added_field["id"] ?>][id]' value='<?php echo $one_post_connector_added_field["id"] ?>' />
										<input type='hidden' name='added_fields[<?php echo $one_post_connector_added_field["id"] ?>][name]' value='<?php echo $one_post_connector_added_field["name"] ?>' />
										<input type='hidden' name='added_fields[<?php echo $one_post_connector_added_field["id"] ?>][deleted]' value='0' class="simple-fields-post-connector-added-field-deleted" />
										<div class="simple-fields-post-connector-addded-fields-options">
											Context
											<select name='added_fields[<?php echo $one_post_connector_added_field["id"] ?>][context]' class="simple-fields-post-connector-addded-fields-option-context">
												<option <?php echo ("normal" == $one_post_connector_added_field["context"]) ? " selected='selected' " : "" ?> value="normal">normal</option>
												<option <?php echo ("advanced" == $one_post_connector_added_field["context"]) ? " selected='selected' " : "" ?> value="advanced">advanced</option>
												<option <?php echo ("side" == $one_post_connector_added_field["context"]) ? " selected='selected' " : "" ?> value="side">side</option>
											</select>
											
											Priority
											<select name='added_fields[<?php echo $one_post_connector_added_field["id"] ?>][priority]' class="simple-fields-post-connector-addded-fields-option-priority">
												<option <?php echo ("low" == $one_post_connector_added_field["priority"]) ? " selected='selected' " : "" ?> value="low">low</option>
												<option <?php echo ("high" == $one_post_connector_added_field["priority"]) ? " selected='selected' " : "" ?> value="high">high</option>
											</select>
										</div>
										<a href='#' class='simple-fields-post-connector-addded-fields-delete'>Delete</a>
									</li>
									<?php
								}
								?>
							</ul>
						</td>
					</tr>
					
					<tr>
						<th>
							Available for post types
						</th>
						<td>
							<table>
								<?php
								global $wp_post_types;
								$arr_post_types_to_ignore = array("attachment", "revision", "nav_menu_item");
								foreach ($wp_post_types as $one_post_type) {
									if (!in_array($one_post_type->name, $arr_post_types_to_ignore)) {
										?>
										<tr>
											<td>
												<input <?php echo (in_array($one_post_type->name, $post_connector_in_edit["post_types"]) ? " checked='checked' " : ""); ?> type="checkbox" name="post_types[]" value="<?php echo $one_post_type->name ?>" />
												<?php echo $one_post_type->name ?>
											</td>
											<!-- <td>
												<input <?php echo (in_array($one_post_type->name, $post_connector_in_edit["post_types_type_default"]) ? " checked='checked' " : "") ?> type="checkbox" name="post_types_type_default[]" value="<?php echo $one_post_type->name ?>" />
												Default connector for post type <?php echo $one_post_type->name ?>
											</td> -->
										</tr>
										<?php
									}
								}
								?>
							</table>
						</td>
					</tr>

				</table>
				<p class="submit">
					<input class="button-primary" type="submit" value="Save Changes" />
					<input type="hidden" name="action" value="update" />
					<!-- <input type="hidden" name="page_options" value="field_group_name" /> -->
					<input type="hidden" name="post_connector_id" value="<?php echo $post_connector_in_edit["id"] ?>" />
					or 
					<a href="<?php echo EASY_FIELDS_FILE ?>">cancel</a>
				</p>
				<p class="simple-fields-post-connector-delete">
					<a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=delete-post-connector&amp;connector-id=<?php echo $post_connector_in_edit["id"] ?>">Delete</a>
				</p>

			</form>
			<?php
		}

	
		/**
		 * edit new or existing group
		 */
		if ("edit-field-group" == $action) {
	
			$field_group_id = (int) $_GET["group-id"];
			$highest_field_id = 0;
	
			// if new, save it as unnamed, and then set to edit that
			if ($field_group_id == 0) {
				foreach ($field_groups as $oneGroup) {
					if ($oneGroup["id"]>$highest_id) {
						$highest_id = $oneGroup["id"];
					}
				}
				$highest_id++;
				$field_group_id = $highest_id;
				
				$field_groups[$field_group_id] = array(
					"id" => $field_group_id,
					"name" => "Unnamed field group $field_group_id",
					"repeatable" => false,
					"fields" => array(),
					"deleted" => false
				);
				
				update_option("simple_fields_groups", $field_groups);

			} else {
				// existing field group
				// get highest group and field id
				foreach ($field_groups[$field_group_id]["fields"] as $one_field) {
					if ($one_field["id"] > $highest_field_id) {
						$highest_field_id = $one_field["id"];
					}
				}
			}
			
			$field_group_in_edit = $field_groups[$field_group_id];
			
			?>
			<form method="post" action="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-field-group-save">
				<?php #settings_fields('simple_fields_options'); ?>
	            <h3>Field group details</h3>
	            <table class="form-table">
	            	<tr>
	            		<th><label for="field_group_name">Name</label></th>
	            		<td>
	            			<input type="text" name="field_group_name" id="field_group_name" class="regular-text" value="<?php echo esc_html($field_group_in_edit["name"]) ?>" />
	            			<br />	
	            			<label for="field_group_repeatable">
								<input type="checkbox" <?php echo ($field_group_in_edit["repeatable"] == true) ? "checked='checked'" : ""; ?> value="1" id="field_group_repeatable" name="field_group_repeatable" />
								Repeatable
							</label>
	
	            		</td>
	            	</tr>
	            	<tr>
	            		<th>Fields</th>
	            		<td>
	            			<div id="simple-fields-field-group-existing-fields">
	            				<ul class='simple-fields-edit-field-groups-added-fields'>
									<?php
									foreach ($field_group_in_edit["fields"] as $oneField) {
										if (!$oneField["deleted"]) {
											echo simple_fields_field_group_add_field_template($oneField["id"], $field_group_in_edit);
										}
									}
									?>
	            				</ul>
	            			</div>
	            			<p><a href="#" id="simple-fields-field-group-add-field">+ Add field</a></p>
	            		</td>
	            	</tr>			
				</table>

				<p class="submit">
					<input class="button-primary" type="submit" value="Save Changes" />
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="field_group_name" />
					<input type="hidden" name="field_group_id" value="<?php echo $field_group_in_edit["id"] ?>" />
					or 
					<a href="<?php echo EASY_FIELDS_FILE ?>">cancel</a>
				</p>
				<p class="simple-fields-field-group-delete">
					<a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=delete-field-group&amp;group-id=<?php echo $field_group_in_edit["id"] ?>">Delete</a>
				</p>
				
			</form>
	
			<script type="text/javascript">
				var simple_fields_highest_field_id = <?php echo (int) $highest_field_id ?>;
			</script>
	
			<?php
		
		}


		// overview, if no action
		if (!$action) {


			/**
			 * view post connectors
			 */
			$post_connector_count = 0;
			foreach ($post_connectors as $onePostConnector) {
				if (!$onePostConnector["deleted"]) {
					$post_connector_count++;
				}
			}


			/**
			 * view existing field groups
			 */	
			?>
			<div class="simple-fields-edit-field-groups">

				<h3>Field groups</h3>

				<?php
				if (isset($simple_fields_did_save) && $simple_fields_did_save) {
					?><div id="message" class="updated"><p>Field group saved</p></div><?php
				} elseif (isset($simple_fields_did_delete) && $simple_fields_did_delete) {
					?><div id="message" class="updated"><p>Field group deleted</p></div><?php
				} elseif (isset($simple_fields_did_delete_post_connector) && $simple_fields_did_delete_post_connector) {
					?><div id="message" class="updated"><p>Post connector deleted</p></div><?php
				} elseif (isset($simple_fields_did_save_post_type_defaults) && $simple_fields_did_save_post_type_defaults) {
					?><div id="message" class="updated"><p>Post type defaults saved</p></div><?php
				}

				
				
				$field_group_count = 0;
				foreach ($field_groups as $oneFieldGroup) {
					if (!$oneFieldGroup["deleted"]) {
						$field_group_count++;
					}
				}

				if ($field_groups == $field_group_count) {
					echo "<p>No field groups yet.</p>";
				} else {
					echo "<ul class=''>";
					foreach ($field_groups as $oneFieldGroup) {
						if (!$oneFieldGroup["deleted"]) {
							echo "<li><a href='" . EASY_FIELDS_FILE . "&amp;action=edit-field-group&amp;group-id=$oneFieldGroup[id]'>$oneFieldGroup[name]</a></li>";
						}
					}
					echo "</ul>";
				}
				echo "<p><a class='button' href='" . EASY_FIELDS_FILE . "&amp;action=edit-field-group&amp;group-id=0'>+ New field group</a></p>";
				?>
			</div>
		
		
			<div class="simple-fields-edit-post-connectors">

				<h3>Post Connectors</h3>

				<?php
				if (isset($simple_fields_did_save_connector) && $simple_fields_did_save_connector) {
					?><div id="message" class="updated"><p>Post connector saved</p></div><?php
				}

				if ($post_connector_count) {
					?><ul><?php
						foreach ($post_connectors as $one_post_connector) {
							if ($one_post_connector["deleted"]) {
								continue;
							}
							?>
							<li>
								<a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-post-connector&amp;connector-id=<?php echo $one_post_connector["id"] ?>"><?php echo $one_post_connector["name"] ?></a>
							</li>
							<?php
							
						}
					?></ul><?php
				} else {
					?>
					<!-- <p>No post connectors</p> -->
					<?php
				}
				?>
				<p>
					<a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-post-connector&amp;connector-id=0" class="button">+ New post connector</a>
				</p>
				
			</div>

			<div class="easy-fields-post-type-defaults">
				<h3>Post type defaults</h3>
				<?php
				#$post_types = get_post_types();
				#d($post_types);
				?>
				<ul>
					<?php
					$post_types = get_post_types();
					$arr_post_types_to_ignore = array("attachment", "revision", "nav_menu_item");
					foreach ($post_types as $one_post_type) {
						$one_post_type_info = get_post_type_object($one_post_type);
						#d($one_post_type_info);
						if (!in_array($one_post_type, $arr_post_types_to_ignore)) {
							?><li>
								<a href="<?php echo EASY_FIELDS_FILE ?>&amp;action=edit-post-type-defaults&amp;post-type=<?php echo $one_post_type ?>"><?php echo $one_post_type_info->label ?></a>
							</li><?php
						}
					}
					?>
				</ul>
			</div>	

			<?php

		} // end simple_fields_options

		?>

	</div>	

	<?php
} // end func simple_fields_options

function simple_fields_field_group_add_field() {
	$simple_fields_highest_field_id = (int) $_POST["simple_fields_highest_field_id"];
	echo simple_fields_field_group_add_field_template($simple_fields_highest_field_id);
	exit;
}

function simple_fields_field_group_add_field_template($fieldID, $field_group_in_edit = null) {
	$fields = $field_group_in_edit["fields"];
	$field_name = esc_html($fields[$fieldID]["name"]);
	$field_description = esc_html($fields[$fieldID]["description"]);
	$field_type = $fields[$fieldID]["type"];
	$field_deleted = (int) $fields[$fieldID]["deleted"];
	
	$field_type_textarea_option_use_html_editor = (int) @$fields[$fieldID]["type_textarea_options"]["use_html_editor"];
	$field_type_checkbox_option_checked_by_default = (int) @$fields[$fieldID]["type_checkbox_options"]["checked_by_default"];
	$field_type_radiobuttons_options = (array) @$fields[$fieldID]["type_radiobuttons_options"];
	$field_type_dropdown_options = (array) @$fields[$fieldID]["type_dropdown_options"];
	
	#d($field_type_radiobuttons_options);
	
	$out = "";
	$out .= "
	<li class='simple-fields-field-group-one-field simple-fields-field-group-one-field-id-{$fieldID}'>
		<div class='simple-fields-field-group-one-field-handle'></div>
		<div class='simple-fields-field-group-one-field-row'>
			<label class='simple-fields-field-group-one-field-name-label'>Name</label>
			<!-- <br /> -->
			<input type='text' class='regular-text simple-fields-field-group-one-field-name' name='field[{$fieldID}][name]' value='{$field_name}' />
		</div>
		<div class='simple-fields-field-group-one-field-row simple-fields-field-group-one-field-row-description'>
			<label>Description</label>
			<!-- <br /> -->
			<input type='text' class='regular-text' name='field[{$fieldID}][description]' value='{$field_description}' />
		</div>
		<div class='simple-fields-field-group-one-field-row'>
			<label>Type</label>
			<!-- <br /> -->
			<select name='field[{$fieldID}][type]' class='simple-fields-field-type'>
				<option value=''>Select...</option>
				<option value='text'" . (($field_type=="text") ? " selected='selected' " : "") . ">Text</option>
				<option value='textarea'" . (($field_type=="textarea") ? " selected='selected' " : "") . ">Textarea</option>
				<option value='checkbox'" . (($field_type=="checkbox") ? " selected='selected' " : "") . ">Checkbox</option>
				<option value='radiobuttons'" . (($field_type=="radiobuttons") ? " selected='selected' " : "") . ">Radio buttons</option>
				<option value='dropdown'" . (($field_type=="dropdown") ? " selected='selected' " : "") . ">Dropdown</option>
				<option value='file'" . (($field_type=="file") ? " selected='selected' " : "") . ">File</option>
			</select>

			<div class='simple-fields-field-group-one-field-row " . (($field_type=="text") ? "" : " hidden ") . " simple-fields-field-type-options simple-fields-field-type-options-text'>
			</div>
		</div>

		<div class='simple-fields-field-group-one-field-row " . (($field_type=="textarea") ? "" : " hidden ") . " simple-fields-field-type-options simple-fields-field-type-options-textarea'>
			<input type='checkbox' name='field[{$fieldID}][type_textarea_options][use_html_editor]' " . (($field_type_textarea_option_use_html_editor) ? " checked='checked'" : "") . " value='1' /> Use HTML-editor
		</div>
		";
		
		// radiobuttons
		$radio_buttons_added = "";
		$radio_buttons_highest_id = 0;
		if ($field_type_radiobuttons_options) {
			foreach ($field_type_radiobuttons_options as $key => $val) {
				if (strpos($key, "radiobutton_num_") !== false && $val["deleted"] != true) {
					// found one button in format radiobutton_num_0
					$radiobutton_num = str_replace("radiobutton_num_", "", $key);
					if ($radiobutton_num > $radio_buttons_highest_id) {
						$radio_buttons_highest_id = $radiobutton_num;
					}
					$radiobutton_val = esc_html($val["value"]);
					$checked = ($key == $field_type_radiobuttons_options["checked_by_default_num"]) ? " checked='checked' " : "";
					$radio_buttons_added .= "
						<li>
							<div class='simple-fields-field-type-options-radiobutton-handle'></div>
							<input class='regular-text' value='$radiobutton_val' name='field[$fieldID][type_radiobuttons_options][radiobutton_num_{$radiobutton_num}][value]' type='text' />
							<input class='simple-fields-field-type-options-radiobutton-checked-by-default-values' type='radio' name='field[$fieldID][type_radiobuttons_options][checked_by_default_num]' value='radiobutton_num_{$radiobutton_num}' {$checked} />
							<input class='simple-fields-field-type-options-radiobutton-deleted' name='field[$fieldID][type_radiobuttons_options][radiobutton_num_{$radiobutton_num}][deleted]' type='hidden' value='0' />
							<a href='#' class='simple-fields-field-type-options-radiobutton-delete'>Delete</a>
						</li>";
				}
			}
		}
		$radio_buttons_highest_id++;
		$out .= "
			<div class='" . (($field_type=="radiobuttons") ? "" : " hidden ") . " simple-fields-field-type-options simple-fields-field-type-options-radiobuttons'>
				<div>Added radio buttons</div>
				<div class='simple-fields-field-type-options-radiobutton-checked-by-default'>Default</div>
				<ul class='simple-fields-field-type-options-radiobutton-values-added'>
					$radio_buttons_added
				</ul>
				<div><a class='simple-fields-field-type-options-radiobutton-values-add' href='#'>+ Add radio button</a></div>
				<input type='hidden' name='' class='simple-fields-field-group-one-field-radiobuttons-highest-id' value='{$radio_buttons_highest_id}' />
			</div>
		";
		// end radiobuttons

		// checkbox
		$out .= "
		<div class='simple-fields-field-group-one-field-row " . (($field_type=="checkbox") ? "" : " hidden ") . " simple-fields-field-type-options simple-fields-field-type-options-checkbox'>
			<input type='checkbox' name='field[{$fieldID}][type_checkbox_options][checked_by_default]' " . (($field_type_checkbox_option_checked_by_default) ? " checked='checked'" : "") . " value='1' /> Checked by default
		</div>
		";
		// end checkbox

		// start dropdown
		$dropdown_values_added = "";
		$dropdown_values_highest_id = 0;
		if ($field_type_dropdown_options) {
			foreach ($field_type_dropdown_options as $key => $val) {
				if (strpos($key, "dropdown_num_") !== false && $val["deleted"] != true) {
					// found one button in format radiobutton_num_0
					$dropdown_num = str_replace("dropdown_num_", "", $key);
					if ($dropdown_num > $dropdown_values_highest_id) {
						$dropdown_values_highest_id = $dropdown_num;
					}
					$dropdown_val = esc_html($val["value"]);
					$dropdown_values_added .= "
						<li>
							<div class='simple-fields-field-type-options-dropdown-handle'></div>
							<input class='regular-text' value='$dropdown_val' name='field[$fieldID][type_dropdown_options][dropdown_num_{$dropdown_num}][value]' type='text' />
							<input class='simple-fields-field-type-options-dropdown-deleted' name='field[$fieldID][type_dropdown_options][dropdown_num_{$dropdown_num}][deleted]' type='hidden' value='0' />
							<a href='#' class='simple-fields-field-type-options-dropdown-delete'>Delete</a>
						</li>";
				}
			}
		}
		$dropdown_values_highest_id++;
		$out .= "
			<div class='" . (($field_type=="dropdown") ? "" : " hidden ") . " simple-fields-field-type-options simple-fields-field-type-options-dropdown'>
				<div>Added dropdown values</div>
				<ul class='simple-fields-field-type-options-dropdown-values-added'>
					$dropdown_values_added
				</ul>
				<div><a class='simple-fields-field-type-options-dropdown-values-add' href='#'>+ Add dropdown value</a></div>
				<input type='hidden' name='' class='simple-fields-field-group-one-field-dropdown-highest-id' value='{$dropdown_values_highest_id}' />
			</div>
		";
		// end dropdown


		$out .= "
		<div class='delete'>
			<a href='#'>Delete field</a>
		</div>
		<input type='hidden' name='field[{$fieldID}][id]' class='simple-fields-field-group-one-field-id' value='{$fieldID}' />
		<input type='hidden' name='field[{$fieldID}][deleted]' value='{$field_deleted}' class='hidden_deleted' />

	</li>";
	return $out;

}

