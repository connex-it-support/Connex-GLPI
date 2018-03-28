<?php

/**
 *This is a heavily modified checkboxesfield class to incorporate prices and quantities to each item. 

 *It has been changed to allow users to pick quantities of items and shows the cost of each item rather than just having an option to check an item.
 *Initially made with the upgrade items field of the New Hire Form but was designed so it can be a general list of priced items.
 *
 *If there are no quantities set intially for this question, then the question will become a dropdown selection instead.
 *For Desktop/Laptop field but can be incoroporated for other dropdown/select menus with priced items
 *
 *Modifies p tags to add costs and totals. 
 *	id = "total" -> in form.class.php -> Each price field (below) will be added to the total as well as its corresponding text to the total text which
 * 	will be displayed at the bottom of the form.
 *
 *	id = "cost_" + id -> Each price field creates a p tag with this id. It will output the cost of the selected items and the bottom of that field
 
 */
class PluginFormcreatorPriceField extends PluginFormcreatorField
{
	const IS_MULTIPLE    = true;
	const MAX_QUANTITY	 = 3;
	
 /**Sets up the field for display. Also adds and modifies the costs paragraphs.
  */
 public function displayField($canEdit = true) {
 	$rand       = mt_rand();
 	
 	
 	if ($canEdit) {
 		
 		
		 //Get the values and prices of the field
 		$values = [];
 		$values = $this->getAvailableValues();
 		$prices = [];
 		$prices = $this->getAvailablePrices();
 		$details = [];
 		$details = $this->getDetails();
 		$tab_values = [];
 		$fieldID = $this->fields['id'];
 		
 		if (!empty($values)) {
 			
			//has quantities, so will make list with inputs for quantities
 			if ($this->fields['quantity'] != null){
 				echo '<div class="checkboxes">';
				//Table setup for item list table
				echo '<table width ="80%" align="center">';
 				echo '<tr><td width="50%" align="center">Item</td><td>Cost</td><td>Quantity</td></tr>';
 				$i = 0;
 				
 				//For each values/item, create a row in the table
 				foreach ($values as $key => $value)  {
 					echo '<tr><td width="50%" >';
 					if ((trim($value) != '')) {
 						$i++;

 						$current_value = null;
 						$current_value = $this->getValue();
 						echo "<div class='checkbox'>";
 						

 						//Create label for the item. Only addes the price if it exists for the item
 						echo '<label for="formcreator_field_'.$this->fields['id'].'_'.$i.'">';
 						
 						if ($prices[$key] != null){  
 							echo '&nbsp;' .$value . '</td><td> $' . $prices[$key];
 						} else{
 							echo '&nbsp;' .$value. '</td><td>';
 						}
 						
 						echo '</label>';
				  echo '</div></td><td>'; //div ends checkbox
				  
				  //add quantity input
				  echo '<input type="number" id="formcreator_field_'.$this->fields['id'].'_'.$i.'" name="formcreator_field_'.$this->fields['id'].'[]" min="0" max='.self::MAX_QUANTITY.' value="0"  onchange=\'calcTotal()\' style="width: 4em" >';

				}
				echo '</td></tr>';
            } //end for each
            
            
            echo '
            <script type="text/javascript">
            
			//Calculate and display the total cost of upgrades
            function calcTotal(){
            	
				// if any of the inputs in the list are changed, then change the input value (will be sent through $_POST)
            	jQuery(document).ready(function($) {
            		jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").on("change", function() {
            			var tab_values = new Array();
            			
            			jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").each(function() {
            				
            				tab_values.push(this.value);
            				
            			});
            			
            			if (tab_values != null){
            				formcreatorChangeValueOf (' . $this->fields['id'] .', tab_values);
            			}
            		});
            	});
            	
				//set total to 0	
            	var total = parseInt(0);
				//gets the prices of upgrades
            	var price = '.json_encode($prices).';
            	
				//number of prices that are given
            	var length = price.length;
            	
				//get the field value
            	var field = '.$fieldID.';
            	
            	var input = parseInt(1);

            	
				//checks if field is checked and add price to total if it is checked
            	
            	for (i=1; i<length+1; i++){

            		input  = (document.getElementById("formcreator_field_" + field + "_" +i) ).value;

            		var num  = parseInt(price[i-1]);
            		total+= input*num;

            	}//end for
            	
            	var old_value =  getCost("cost_"+field);
            	var total_value = getCost("total");		
            	var total_text = document.getElementById("total").innerHTML;
				
				
				//index to $ of the current field
            	var start = total_text.indexOf("'.$this->fields['name'].' Costs: $");
            	
				var end = -1;
				
				//if start is not -1, then it was found and get index of break after the cost
            	if (start!=-1){ 
            		end = total_text.substring(start).indexOf("<br>") + start; 
            	}
            	
            	//The cost text to be set the p tag at bottom of field.
            	var display = "'.$this->fields['name'].' Costs: $" + total;
            	document.getElementById("cost_"+field).innerHTML = display;
            	
				/*
				* Recalculate total cost. The values are:
				*	1. The newest value of only the current field.
				*	2. The previous total cost of the whole form.
				*	3. The previous value of the current field. 
				*/
            	total_value =parseInt(total) + parseInt( total_value ) - old_value;
            	
				//If cost of the current field has been calculated before, replace the old version with the updated version
            	if (end>=0){
            		total_text =total_text.replace(total_text.substring(start,end), display);
            	}
            	
				//If previous current cost has been calculated before, only apply the updated text to the total p tag.
            	if (start != -1){
            		document.getElementById("total").innerHTML= total_text.substring(0,total_text.indexOf("Total Cost: $")) + "Total Cost: $" +total_value;
            		return;
				//This field has not been added to the total text yet so add it.
            	} else{
            		document.getElementById("total").innerHTML= total_text.substring(0,total_text.indexOf("Total Cost: $"))  + display + "<br>"+ "Total Cost: $" +total_value;
            		return;
            	}

            } //end function
            </script>';
            
			} else{ //price field has no quantities, so make dropdown instead
				echo '<div class="form_field">';

				if (!empty($this->fields['values'])) {
					foreach ($values as $value) {
						if ((trim($value) != '')) {
							$tab_values[$value] = $value;
						}
					}
					
					//if field allows empty, add it to the list of values
					if ($this->fields['show_empty']) {
						$tab_values = ['' => '-----'] + $tab_values;
					}
					
					// Create dropdown
					Dropdown::showFromArray('formcreator_field_' . $this->fields['id'], $tab_values, [
						'value'     => $this->fields['answer'],
						'values'    => [],
						'rand'      => $rand,
						'multiple'  => false,
						'on_change'	=> 'calcSingle()',
					]);
				}
				//script for changing value in the database if it has no quantities
				echo '<script type="text/javascript">
				jQuery(document).ready(function($) {
					jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("change", function(e) {
						var selectedValues = jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").val();
						formcreatorChangeValueOf (' . $this->fields['id'] . ', selectedValues);
					});
				});
				</script>';
				
				
				
				
				echo '<script type="text/javascript">
				//Calculate the value of the selected item and add it to total
				function calcSingle(){
					
					
					var field = '.$fieldID.';
					var price = '.json_encode($prices).';
					var item = '.json_encode($values).';
					var rand = '.$rand.';
					var length = price.length;
					
					//get dropdown value
					var str = document.getElementById("dropdown_formcreator_field_" + field + rand).value;


					
					//get old cost value of input and total 
					var old_value = getCost("cost_" + field);
					var total_value = getCost("total"); 
					
					//Set default display if no option is selected
					var display = "'.$this->fields['name'].' Costs: $0<br>";
					
					var total_text =document.getElementById("total").innerHTML;
					
					
					//In the current total text field, set up replacing text
					var start = total_text.indexOf("'.$this->fields['name'].' Costs: $");
					var end = -1;
					if (start!=-1){ 
						end = total_text.substring(start).indexOf("<br>") + start;
					}
					
					//Go through each item until you find a match with the selected value
					for (i =1; i<length+1;i++){
						
						//matched value condition
						if (str == item[i-1]){
							
							
							// Display for details of item
							
							//Gets the detail for the item and lists them out
							var detail = '. json_encode($details). ';
							detail = "Hardware Info:<br><ul><li>" + detail[i-1] + "</ul>";
							detail = detail.replace(/\,/g, "</li><li>");
							document.getElementById("detail_"+field).innerHTML = detail;
							
							//Set display for current section
							display = "'.$this->fields['name'].' Costs: $" +price[i-1];
							document.getElementById("cost_"+field).innerHTML = display;

							//Setting new total cost -> NewFieldCost + TotalValue - OldFieldCost
							total_value =parseInt(price[i-1]) + parseInt( total_value ) - old_value;
							
							//Replace the old text if it exists
							if (end >=0){
								total_text =total_text.replace(total_text.substring(start,end), display);
							}
							
							
							//Set the total text. Include the section text only if it was not found previously. End function
							if (start != -1){
								document.getElementById("total").innerHTML= total_text.substring(0,total_text.indexOf("Total Cost: $")) + "Total Cost: $" +total_value;
								
								return;
							} else{
								document.getElementById("total").innerHTML= total_text.substring(0,total_text.indexOf("Total Cost: $")) + display +"<br>"+ "Total Cost: $" +total_value;
								return;
							}
						}
					}//end for - Item was not found, so must be empty. Will use the default values.
					
					//Set everything to defaults
					document.getElementById("cost_"+field).innerHTML = display;
					total_value = parseInt( total_value ) - old_value;
					total_text =(document.getElementById("total").innerHTML).replace(total_text.substring(start,end), display);
					document.getElementById("total").innerHTML= total_text.substring(0,total_text.indexOf("Total Cost: $")) + "Total Cost: $" +total_value;
					
				} //end function
				
				</script>';
			}

			

			echo '<tr><td colspan="2"></td></tr>';
            echo '</div>'; //end div form field
            echo '</table>';
			echo '<p id ="detail_'.$fieldID.'" ></p>';	
            echo '<p class="cost_text" id ="cost_'.$fieldID.'" ></p>';		

            

        }
        

        
        
    } else if ($this->fields['quantity'] == null){ 
    	echo '<div class="form_field">';
    	echo nl2br($this->getAnswer());
    	echo '</div>' . PHP_EOL;

    }else {
    	$answer = null;
    	$answer = $this->getAnswer();
    	if (!empty($answer)) {
    		if (is_array($answer)) {
    			echo implode("<br />", $answer);
    		} else if (is_array(json_decode($answer))) {
    			echo implode("<br />", json_decode($answer));
    		} else {
    			echo $this->getAnswer();
    		}
    	} else {
    		echo '';
    	}
      } //end else
      
  }

  public function getAnswer() {
  	$values = $this->getAvailableValues();
  	$value  = $this->getValue();
  	return in_array($value, $values) ? $value : $this->fields['default_values'];
  }
  
  
  //If quantity is null, use field.class.php's isValid instead of this one.  
  public function isValid($value) {
  	if ($this->fields['quantity'] == null){
  		return parent::isValid($value);
  		
  	}
  	$value = json_decode($value);
  	if (is_null($value)) {
  		$value = [];
  	}

      // If the field is required it can't be empty
  	if ($this->isRequired() && empty($value)) {
  		Session::addMessageAfterRedirect(
  			__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
  			false,
  			ERROR);
  		return false;

         // Min range not set or number of selected item lower than min
  	} else if (!empty($this->fields['range_min']) && (count($value) < $this->fields['range_min'])) {
  		$message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $this->fields['range_min']);
  		Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
  		return false;

         // Max range not set or number of selected item greater than max
  	} else if (!empty($this->fields['range_max']) && (count($value) > $this->fields['range_max'])) {
  		$message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $this->fields['range_max']);
  		Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
  		return false;

         // All is OK
  	} else {
  		return true;
  	}
  }

  public static function getName() {
  	return __('Prices', 'formcreator');
  }

  public function prepareQuestionInputForSave($input) {
  	
  	if (isset($input['values'])) {
  		if (empty($input['values'])) {
  			Session::addMessageAfterRedirect(
  				__('The field value is required:', 'formcreator') . ' ' . $input['name'],
  				false,
  				ERROR);
  			return [];
  		} else {
  			$input['values'] = $this->trimValue($input['values']);
  			$input['values'] = addslashes($input['values']);
  		}
  	}
  	if (isset($input['default_values'])) {
  		$input['default_values'] = $this->trimValue($input['default_values']);
  		$input['default_values'] = addslashes($input['default_values']);
  	}


  	if (isset($input['prices'])) {
  		$input['prices'] = $this->trimValue($input['prices']);
  		$input['prices'] = addslashes($input['prices']);
  	}
  	

  	return $input;
  }

  public static function getPrefs() {
  	return [
  		'required'       => 1,
  		'default_values' => 1,
  		'values'         => 1,
  		'range'          => 1,
  		'show_empty'     => 0,
  		'regex'          => 0,
  		'show_type'      => 1,
  		'dropdown_value' => 0,
  		'glpi_objects'   => 0,
  		'ldap_values'    => 0,
  		'prices'		 => 1,
  	];
  }

  public static function getJSFields() {
  	$prefs = self::getPrefs();
  	return "tab_fields_fields['price'] = 'showFields(" . implode(', ', $prefs) . ");';";
  }

}
