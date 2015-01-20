

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author user
 */
 class RestfulEntityBaseFieldCollection extends \RestfulEntityBase {

   protected function getTargetTypeFromEntityReference(\EntityMetadataWrapper $wrapper, $property) {
    $params = array('@property' => $property);

    if ($field = field_info_field($property)) {
      if ($field['type'] == 'entityreference') {
        return $field['settings']['target_type'];
      }
      elseif ($field['type'] == 'taxonomy_term_reference') {
        return 'taxonomy_term';
      }
      //The next 2 lines it for field collection 19-01-2015
      elseif ($field['type'] == 'field_collection') {                    
        return 'field_collection_item';
      }

    throw new \RestfulException(format_string('Field @property is not an entity reference or taxonomy reference field.', $params));
   
    }
    else {
      // This is a property referencing another entity (e.g. the "uid" on the
      // node object).
      $info = $wrapper->info();
      if ($this->getEntityInfo($info['type'])) {
        return $info['type'];
      }

      throw new \RestfulException(format_string('Property @property is not defined as reference in the EntityMetadataWrapper definition.', $params));
    }
    
      parent::setPropertyValues($wrapper, $null_missing_fields);
  }
  
   /**
   * Massage the value to set according to the format expected by the wrapper.
   *
   * @param string $property_name
   *   The property name to set.
   * @param $value
   *   The value passed in the request.
   * @param string $public_field_name
   *   The name of the public field to set.
   *
   * @return mixed
   *   The value to set using the wrapped property.
   */
  public function propertyValuesPreprocess($property_name, $value, $public_field_name) {
    // Get the field info.
    $field_info = field_info_field($property_name);

    switch ($field_info['type']) {
      case 'entityreference':
      case 'taxonomy_term_reference':
        return $this->propertyValuesPreprocessReference($property_name, $value, $field_info, $public_field_name);
      //The next 2 lines it for field collection 19-01-2015
      case 'field_collection':
        return $this->propertyValuesPreprocessReference($property_name, $value, $field_info, $public_field_name);


      case 'text':
      case 'text_long':
      case 'text_with_summary':
        return $this->propertyValuesPreprocessText($property_name, $value, $field_info);

      case 'file':
      case 'image':
        return $this->propertyValuesPreprocessFile($property_name, $value, $field_info);
    }

    // Return the value as is.
    return $value;
  }
  
   /**
   * Determines if a field has allowed values.
   *
   * If Field is reference, and widget is autocomplete, so for performance
   * reasons we do not try to grab all the referenced entities.
   *
   * @param array $field
   *   The field info array.
   * @param array $instance
   *   The instance info array.
   *
   * @return bool
   *   TRUE if a field should be populated with the allowed values.
   */
  protected function formSchemaHasAllowedValues($field, $instance) {
    $field_types = array(
      'entityreference',
       //The next line it for field collection 19-01-2015
      'taxonomy_term_reference',
        'field_collection',
    );

    $widget_types = array(
      'taxonomy_autocomplete',
      'entityreference_autocomplete',
      'entityreference_autocomplete_tags',
    );

    return !in_array($field['type'], $field_types) || !in_array($instance['widget']['type'], $widget_types);
  }
}
