<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name: WRW Formidable Forms Extender
 * Plugin URI:
 * Description: Include WRW Calculator into Formidable Pro Fields.
 * Author:      WRW Dev Team
 * Version:     1.0.0
 */


// If this file is accessed directly, abort!
defined('ABSPATH') || die('Unauthorized Access');

// defined('WPINC') || die;
// require_once __DIR__ . '/vendor/autoload.php';


function wrw_formidable_extender_enqueue_scripts()
{

    wp_enqueue_script('jquery');
    wp_enqueue_script('wrw-formidable-script', get_template_directory_uri() . '/js/formidable-custom-script.js',  array('jquery'));

    // Pass the PHP created nonce as an object so it is available in the named JavaScript file.
    $wrw_ajax_object = array(
        'carNonce' => wp_create_nonce('get_car_models'),
        'yearNonce' => wp_create_nonce('get_car_years'),
        'areaNonce' => wp_create_nonce('get_car_area'),
    );

    wp_add_inline_script('wrw-formidable-script', 'var wrw_ajax_object = ' . json_encode($wrw_ajax_object) . ';');
}

add_action('wp_enqueue_scripts', 'wrw_formidable_extender_enqueue_scripts');



// Add a custom field to Formidable Forms
// add_filter('frm_setup_new_fields_vars', 'add_custom_formidable_field');
// add_filter( 'frm_available_fields', 'add_custom_formidable_field' );

add_filter('frm_pro_available_fields', 'add_pro_car_make');
function add_pro_car_make($fields)
{
    $fields['car-make'] = array(
        'name' => 'Car Make',
        'icon' => 'frm_icon_font frm_pencil_icon', // Set the class for a custom icon here.
    );

    return $fields;
}


add_filter('frm_before_field_created', 'set_my_field_defaults2');

function set_my_field_defaults2($field_data)
{
    if ($field_data['type'] == 'car-make') {
        $field_data['name'] = 'What is Car Make?';

        $defaults = array(
            'size' => 400, 'max' => 150,
            'label1' => 'Draw It',
        );

        foreach ($defaults as $k => $v) {
            $field_data['field_options'][$k] = $v;
        }
    }

    return $field_data;
}

add_action('frm_display_added_fields', 'show_the_admin_field2');
function show_the_admin_field2($field)
{
    if ($field['type'] != 'car-make') {
        return;
    }

    $field_name = 'item_meta[' . $field['id'] . ']';
    ?>

    <div class="frm_html_field_placeholder">
        <div class="howto button-secondary frm_html_field">
            This is a placeholder for your signature field.
            <br />View your form to see it in action.
        </div>
    </div> <?php
}

add_filter('frm_update_field_options', 'update_field_options', 10, 3);
function update_field_options($field_options, $field, $values)
{
    if ($field->type != 'car-make')
        return $field_options;

    // Populate options dynamically from get_makes function
    $makes = get_makes();
    $options = array();
    foreach ($makes as $make) {
        $options[sanitize_title($make)] = $make;
    }

    $defaults = array(
        'options' => $options,
    );

    foreach ($defaults as $opt => $default) {
        $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;
    }

    return $field_options;
}

add_action('frm_form_fields', 'show_my_front_field2', 10, 3);
function show_my_front_field2($field, $field_name, $atts)
{
    if ($field['type'] != 'car-make') {
        return;
    }
    $field['value'] = stripslashes_deep($field['value']);

    // Get options dynamically
    //$options = isset( $field['options'] ) ? $field['options'] : array();

    $makes = get_makes();
    $options = array();
    foreach ($makes as $make) {
        $options[sanitize_title($make)] = $make;
    }


    ?>
    <select id="<?php echo esc_attr($field['type']) ?>" name="<?php echo esc_attr($field_name) ?>">
        <option value="">Select Car Make</option>
        <?php foreach ($options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($field['value'], $value); ?>><?php echo esc_html($label); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

add_filter('frm_display_value', 'display_my_field_value2', 10, 3);
function display_my_field_value2($value, $field, $atts)
{
    if ($field->type != 'car-make' || empty($value)) {
        return $value;
    }

    $value = '<div style="color:blue;">' . $value . '</div>';

    return $value;
}


/**
 *
 * Get Car Models AJAX.
 *
 */

add_action('wp_ajax_return_car_models', 'wrw_return_car_models_callback');
add_action('wp_ajax_nopriv_return_car_models', 'wrw_return_car_models_callback');

function wrw_return_car_models_callback()
{
    if (!check_ajax_referer('get_car_models', '_wpnonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    $car_make = isset($_POST['car_make']) ? $_POST['car_make'] : 'N/A';
    $car_models = [];

    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}car_models WHERE make = '{$car_make}'", OBJECT);

    //  var_dump($results);

    // wp_die();

    foreach ($results as $result => $object) {
        array_push($car_models, $object->model);
    }

    $car_models = array_unique($car_models);
    $car_models_element = "<option>Select Car Model</option>";

    foreach ($car_models as $model) {
        $car_models_element .= '<option value="' . $model . '">' . $model . '</option>';
    }

    // echo json_encode($car_models_element);
    //var_dump($car_models_element);
    echo $car_models_element;

    wp_die();
}



add_filter('frm_pro_available_fields', 'add_pro_car_models');
function add_pro_car_models($fields)
{
    $fields['car-models'] = array(
        'name' => 'Car Models',
        'icon' => 'frm_icon_font frm_pencil_icon', // Set the class for a custom icon here.
    );

    return $fields;
}


add_filter('frm_before_field_created', 'set_car_models');
function set_car_models($field_data)
{
    if ($field_data['type'] == 'car-models') {
        $field_data['name'] = 'What is Car Model?';

        $defaults = array(
            'size' => 400, 'max' => 150,
        );

        foreach ($defaults as $k => $v) {
            $field_data['field_options'][$k] = $v;
        }
    }

    return $field_data;
}

add_action('frm_display_added_fields', 'show_the_car_models');
function show_the_car_models($field)
{
    if ($field['type'] != 'car-models') {
        return;
    }

    $field_name = 'item_meta[' . $field['id'] . ']';
    ?>
    <div class="frm_html_field_placeholder">
        <div class="howto button-secondary frm_html_field">
            This is a placeholder for selecting Car Models.
            <br />View your form to see it in action.
        </div>
    </div>
    <?php
}

add_filter('frm_update_field_options', 'update_car_model_options', 10, 3);
function update_car_model_options($field_options, $field, $values)
{
    if ($field->type != 'car-models')
        return $field_options;



    $defaults = array(
        'label1' => __('Draw It', 'formidable'),
        'label2' => __('Type It', 'formidable'),
        'label3' => __('Clear', 'formidable'),
    );

    foreach ($defaults as $opt => $default)
        $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;

    return $field_options;
}

add_action('frm_form_fields', 'show_car_model_frontend', 10, 3);
function show_car_model_frontend($field, $field_name, $atts)
{
    if ($field['type'] != 'car-models') {
        return;
    }
    $field['value'] = stripslashes_deep($field['value']);
    ?>
    <select disabled id="<?php echo esc_attr($field['type']) ?>" name="<?php echo esc_attr($field_name) ?>">
        <!-- You can add a default option if needed -->
        <option value="">Select Car Model</option>
    </select>
    <?php

}




/**
 *
 * Get Car Years AJAX.
 *
 */

add_action('wp_ajax_return_car_years', 'wrw_return_car_years_callback');
add_action('wp_ajax_nopriv_return_car_years', 'wrw_return_car_years_callback');

function wrw_return_car_years_callback()
{
    if (!check_ajax_referer('get_car_years', '_wpnonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }
    $car_make = isset($_POST['car_make']) ? $_POST['car_make'] : 'N/A';
    $car_model = isset($_POST['car_model']) ? $_POST['car_model'] : 'N/A';
    $car_years = [];

    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}car_models WHERE model = '{$car_model}' AND make = '{$car_make}'", OBJECT);

    //  var_dump($results);

    // wp_die();

    foreach ($results as $result => $object) {
        array_push($car_years, $object->year);
    }

    $car_years = array_unique($car_years);
    $car_years_element = "<option>Select Car Year</option>";

    foreach ($car_years as $year) {
        $car_years_element .= '<option value="' . $year . '">' . $year . '</option>';
    }

    // echo json_encode($car_models_element);
    //var_dump($car_models_element);
    echo $car_years_element;

    wp_die();
}



add_filter('frm_pro_available_fields', 'add_pro_car_years');
function add_pro_car_years($fields)
{
    $fields['car-years'] = array(
        'name' => 'Car Years',
        'icon' => 'frm_icon_font frm_pencil_icon', // Set the class for a custom icon here.
    );

    return $fields;
}


add_filter('frm_before_field_created', 'set_car_years');
function set_car_years($field_data)
{
    if ($field_data['type'] == 'car-years') {
        $field_data['name'] = 'Select Car Year';

        $defaults = array(
            'size' => 400, 'max' => 150,
        );

        foreach ($defaults as $k => $v) {
            $field_data['field_options'][$k] = $v;
        }
    }

    return $field_data;
}

add_action('frm_display_added_fields', 'show_the_car_years');
function show_the_car_years($field)
{
    if ($field['type'] != 'car-years') {
        return;
    }

    $field_name = 'item_meta[' . $field['id'] . ']';
?>

<div class="frm_html_field_placeholder">
<div class="howto button-secondary frm_html_field">This is a placeholder for selecting Car Year. <br />View your form to see it in action.</div>
</div> <?php
}

add_filter('frm_update_field_options', 'update_car_year_options', 10, 3);
function update_car_year_options($field_options, $field, $values)
{
    if ($field->type != 'car-years')
        return $field_options;



    $defaults = array(
        'label1' => __('Draw It', 'formidable'),
        'label2' => __('Type It', 'formidable'),
        'label3' => __('Clear', 'formidable'),
    );

    foreach ($defaults as $opt => $default)
        $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;

    return $field_options;
}

add_action('frm_form_fields', 'show_car_year_frontend', 10, 3);
function show_car_year_frontend($field, $field_name, $atts)
{
    if ($field['type'] != 'car-years') {
        return;
    }
    $field['value'] = stripslashes_deep($field['value']);
    ?>
    <select disabled id="<?php echo esc_attr($field['type']) ?>" name="<?php echo esc_attr($field_name) ?>">
        <!-- You can add a default option if needed -->
        <option value="">Select Car Year</option>
    </select>
    <?php

}


/**
 *
 * Get Car AreaAJAX
 *
 */

add_action('wp_ajax_get_car_area', 'wrw_get_car_area_callback');
add_action('wp_ajax_nopriv_get_car_area', 'wrw_get_car_area_callback');

function wrw_get_car_area_callback()
{
    $car_make = isset($_POST['car_make']) ? $_POST['car_make'] : 'N/A';
    $car_model = isset($_POST['car_model']) ? $_POST['car_model'] : 'N/A';
    $car_year = isset($_POST['car_year']) ? $_POST['car_year'] : 'N/A';

    $car_area = 0;
    $car_roof_area = 0;
    $car_area_element = "";

    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}car_models WHERE model = '{$car_model}' AND make = '{$car_make}' AND year = '{$car_year}'", OBJECT);

    foreach ($results as $result => $object) {
        $car_area = $object->area;
        $car_roof_area = $object->roof;
    }


    $car_area_with_roof = $car_area;
    $car_area_without_roof = $car_area - $car_roof_area;

    if (($car_area_with_roof - $car_area_without_roof) !== 0) {
        $car_area_element .= '<option value="">Select Roof Option</option>';
        $car_area_element .= '<option value="' . $car_area_without_roof . '">No Roof Included - ' . $car_area_without_roof . ' sq. ft.</option>';
        $car_area_element .= '<option value="' . $car_area_with_roof . '">Roof Included - ' . $car_area_with_roof . ' sq. ft.</option>';
    } else {
        $car_area_element = "0," . $car_area_with_roof;
    }

    echo $car_area_element;

    wp_die();
}

add_filter('frm_pro_available_fields', 'add_pro_car_area');
function add_pro_car_area($fields)
{
    $fields['car-area'] = array(
        'name' => 'Display Car\'s Total Area',
        'icon' => 'frm_icon_font frm_pencil_icon', // Set the class for a custom icon here.
    );

    return $fields;
}


add_filter('frm_before_field_created', 'set_car_area');
function set_car_area($field_data)
{
    if ($field_data['type'] == 'car-area') {
        $field_data['name'] = 'Car\'s Total Area';

        $defaults = array(
            'size' => 400, 'max' => 150,
        );

        foreach ($defaults as $k => $v) {
            $field_data['field_options'][$k] = $v;
        }
    }

    return $field_data;
}

add_action('frm_display_added_fields', 'show_the_car_area');
function show_the_car_area($field)
{
    if ($field['type'] != 'car-area') {
        return;
    }

    $field_name = 'item_meta[' . $field['id'] . ']';
    ?>

    <div class="frm_html_field_placeholder">
        <div class="howto button-secondary frm_html_field">
            This is a placeholder for Displaying Car Area.
            <br />View your form to see it in action.
        </div>
    </div>
    <?php
}

add_filter('frm_update_field_options', 'update_car_area_options', 10, 3);
function update_car_area_options($field_options, $field, $values)
{
    if ($field->type != 'car-area')
        return $field_options;



    $defaults = array(
        "value" => ''
    );

    foreach ($defaults as $opt => $default)
        $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;

    return $field_options;
}

add_action('frm_form_fields', 'show_car_area_frontend', 10, 3);
function show_car_area_frontend($field, $field_name, $atts)
{
    if ($field['type'] != 'car-area') {
        return;
    }
    $field['value'] = stripslashes_deep($field['value']);
    ?>
    <div>
        <div id="car-area-select-container" class="hidden frm_style_landing-1 with_frm_style form-field">
            <label name="car-area-select">
                Select Roof Type
            </label>
            <select class="hidden" id="car-area-select" name="car-area-select">
                <!-- You can add a default option if needed -->
                <option value="">Select Roof Type</option>
            </select>
        </div>
        <label name="<?php echo esc_attr($field['type']) ?>">
            Total Calculated Car Area (sq ft)
        </label>
        <input type="text" readonly id="<?php echo esc_attr($field['type']) ?>" name="<?php echo esc_attr($field_name) ?>" value="<?php echo esc_attr($field['value']) ?>" />
    </div>
    <?php

}
