<?php
class PluginFormcreatorPriceField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;
   public function displayField($canEdit = true) {
	   
	   
      if ($canEdit) {
		
		 
         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

         $values = [];
         $values = $this->getAvailableValues();
		 $prices = [];
		 $prices = $this->getAvailablePrices();
         
		 if (!empty($values)) {
            echo '<div class="checkboxes">';
			
			//Top header
			echo '<tr><td width="50%" align="center">Item</td><td>Cost</td><td>Quantity</td></tr>';
            $i = 0;
			$fieldID = $this->fields['id'];
            foreach ($values as $key => $value)  {
				 echo '<tr><td width="50%" >';
               if ((trim($value) != '')) {
                  $i++;

                  $current_value = null;
				  $current_value = $this->getValue();
                  echo "<div class='checkbox'>";
				  

										  
                  echo '<label for="formcreator_field_'.$this->fields['id'].'_'.$i.'">';
				  
				
				 if ($prices[$key] != null){  
                  echo '&nbsp;' .$value . '</td><td> $' . $prices[$key];
				  } else{
				  echo '&nbsp;' .$value. '</td><td>';
				  }
				  
                  echo '</label>';
				  echo '</div></td><td>';
				  
				  //add quantity field
				  echo '<input type="number" id="'.'formcreator_field_'.$this->fields['id'].'_'.$i.'" name="formcreator_field_'.$this->fields['id'].'[]" min="0" max="9" value="0"  onchange=\'calcTotal()\' style="width: 4em" >';

               }
				echo '</td></tr>';
            } //end for each
			
			
			
			echo '
			<script type="text/javascript">
			//Calculate and display the total cost of upgrades
			function calcTotal(){
				//set total to 0	
				var total = parseInt(0);
				//gets the prices of upgrades
				var price = '.json_encode($prices).';

				var length = price.length;
							
				//get the field value
				var field = '.$fieldID.';

				//checks if field is checked and add price to total if it is checked
				
				for (i=1; i<length+1; i++){
					var qnt  = (document.getElementById("formcreator_field_"+ field + "_"+i) ).value;
					var num  = parseInt(price[i-1]);
					
						total+= qnt*num;

		
				}//end for
				
				//Set up total to not display the total only if total is 0 or it is not a number
				if (total == 0 || isNaN(total) ){
				document.getElementById("total").innerHTML = ""; 
				} else{
					document.getElementById("total").innerHTML = "Total Upgrade Costs: $" +total; 
				}
			
			} //end function
					
			</script>';
	
			echo '<tr><td colspan="2"><p id ="total" ></p></td></tr>';
			echo '<p id="test"></p>';
            echo '</div>';
			echo '</table>';			
         }
		 
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").on("change", function() {
                        var tab_values = new Array();
						
                        jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").each(function() {
								
                              tab_values.push(this.value);
                           
                        });
						document.getElementById("test").innerHTML ='. $this->fields['id'] .'; 
                        formcreatorChangeValueOf (' . $this->fields['id']. ', tab_values);
                     });
                  });
               </script>';

      } else {
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

   public function isValid($value) {
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
          // $input['values'] = addslashes($input['values']);
         }
      }
      if (isset($input['default_values'])) {
         $input['default_values'] = $this->trimValue($input['default_values']);
       //  $input['default_values'] = addslashes($input['default_values']);
      }


	  if (isset($input['prices'])) {
		$input['prices'] = $this->trimValue($input['prices']);
	//	$input['prices'] = addslashes($input['prices']);
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
		 'prices'		  => 1,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['price'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
