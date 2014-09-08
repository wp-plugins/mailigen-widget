<?php

/*
  Plugin Name: Mailigen Widget
  Plugin URI: http://www.mailigen.com/assets/files/resources/plugins/wordpress/mailigen-widget.zip
  Description: Adds Mailigen signup form to your sidebar.
  Version: 1.1.2
  Author: Mailigen
  Author URI: http://www.mailigen.com

  Copyright 2013 Mailigen (email: info@mailigen.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'MGAPI.class.php';

/**
 * ---------------------
 * MAILIGEN WIDGET
 * ---------------------
 */
class Mailigen_Widget extends WP_Widget {

    /**
     * Options
     */
    var $options;

    /**
     * Constructor
     */
    function Mailigen_Widget() {

        # Get options, if failed return empty array
        $this->options = get_option('mailigen_options', array());

        # Set widget params
        $params = array(
            'classname' => 'MailigenWidget',
            'description' => 'Adds Mailigen signup form to your sidebar'
        );
        # Register widget
        $this->WP_Widget(
                'mailigen_widget', 'Mailigen Signup Form', $params
        );
        # Load external scripts (css)
        wp_enqueue_style(
                'mailigen_css', plugins_url('css' . DIRECTORY_SEPARATOR . 'mailigen.css', __FILE__)
        );
        # Load external scripts (js)
        wp_enqueue_script(
                'mailigen_js', plugins_url('js' . DIRECTORY_SEPARATOR . 'mailigen.js', __FILE__), array('jquery')
        );
        # Ajax hook
        add_action(
                'wp_ajax_mailigen_subscribe', array(&$this, 'subscribe')
        );
    }

    /**
     * Get option by name
     */
    function getOption($name) {

        return isset($this->options[$name]) ? $this->options[$name] : false;
    }

    /**
     * Send data and make subscribtion
     */
    function subscribe() {
        $response = array(
            'success' => false,
            'message' => null
        );

        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $fields = array();
            foreach ($_POST as $key => $value) {
                if (!in_array($key, array('action'))) {
                    $fields[$key] = esc_attr($value);
                }
            }
            $apiKey = $this->getOption('mg_apikey');
            $listId = $this->getOption('mg_fields_list');
            $errors = $this->validate($fields);

            if (count($errors) > 0) {
                $response['message'] = 'Some fields require proper input!';
                $response['errors'] = $errors;
                die(json_encode($response));
            }

            $api = new MGAPI($apiKey);
            $result = $api->listSubscribe($listId, $fields['EMAIL'], $fields, 'html', true, false, true);

            if ($api->errorCode) {
                $response['message'] = $api->errorMessage . ' (#' . $api->errorCode . ')';
            } else {
                $response['success'] = true;
                $response['message'] = array('content' => '
                    <div class="signup-thanks-box">
                        <h2>Almost Finished...</h2>
                        <p>We need to confirm your email address.</p>
                        <p>To complete the subscription process, please click the link in the email we just sent you.</p>                    
                    </div>
                ');
            }
        }
        die(json_encode($response));
    }

    /**
     * Validate fields
     */
    function validate($fields = array()) {

        $errors = array();
        $params = $this->getOption('mg_fields');

        foreach ($fields as $tag => $value) {

            $type = $params[$tag][1];
            $req = $params[$tag][2];

            // check if field is required
            if ($req == 1 && strlen($value) < 1) {
                $errors[$tag] = '* Field required!';
            }
            // validate email address
            if (strlen($value) > 0 && $type == 'email' && !eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $value)) {
                $errors[$tag] = '* Invalid E-mail address!';
            }
            // check numeric
            if (strlen($value) > 0 && $type == 'number' && !eregi('^[0-9]+$', $value)) {
                $errors[$tag] = '* Invalid number!';
            }
            // check text
            if (strlen($value) > 0 && strlen(trim($value)) < 1 && $type == 'text') {
                $errors[$tag] = '* Invalid text!';
            }
        }
        return $errors;
    }

    /**
     * Update widget settings
     */
    function update($new_instance, $old_instance) {

        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['button_name'] = strip_tags($new_instance['button_name']);
        return $instance;
    }

    /**
     * Show widget form in backend
     */
    function form($instance) {

        $instance = wp_parse_args(
                (array) $instance, array(
            'title' => __('Signup For Our Mailing List'),
            'button_name' => __('Subscribe'),
                )
        );
        list( $id, $name, $value ) = array(
            $this->get_field_id('title'),
            $this->get_field_name('title'),
            esc_attr($instance['title'])
        );
        echo "<p>";
        echo "<label for='{$id}'>";
        echo "Title: <input id='{$id}' name='{$name}' type='text' value='{$value}' class='widefat' />";
        echo "</label>";
        echo "</p>";

        list( $id, $name, $value ) = array(
            $this->get_field_id('button_name'),
            $this->get_field_name('button_name'),
            esc_attr($instance['button_name'])
        );
        echo "<p>";
        echo "<label for='{$id}'>";
        echo "Button Name: <input id='{$id}' name='{$name}' type='text' value='{$value}' class='widefat' />";
        echo "</label>";
        echo "</p>";
    }

    /**
     * Show widget in frontend
     */
    function widget($args, $instance) {

        extract($args, EXTR_SKIP);

        echo empty($instance['title']) ? ' ' : $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
        $button_name = empty($instance['button_name']) ? __('Subscribe') : apply_filters('widget_button_name', $instance['button_name']);

        if (!$this->options['mg_fields']) {
            echo "Please set up your Mailigen plugin!";
            return;
        };
        echo $before_widget;

        echo "<form id='mg-widget-form' method='post' action=''>";
        echo "<input type='hidden' name='action' value='mailigen_subscribe'>";
        echo '<div class="mg-error-box">&nbsp;</div>';
        echo "<dl class='mailigen-form'>";
        foreach ($this->options['mg_fields'] as $name => $field) {
            $req = $field[2] == 1 ? '* ' : '';
            echo "<dt><label for='{$name}'>{$req}{$field[0]}</label></dt>";
            echo "<dd><input id='{$name}' type='text' name='{$name}' maxlength='100' /></dd>";
        }
        echo "</dl>";
        echo "<dd><input id='mailigen-submit' type='submit' name='mailigen_submit' value='" . $button_name . "' /></dd>";
        echo "</form>";

        echo $after_widget;
    }

}

add_action(
        'widgets_init', create_function('', 'register_widget( "Mailigen_Widget" );')
);

/**
 * ---------------------
 * MAILIGEN OPTIONS PAGE
 * ---------------------
 */
class Mailigen_Options {

    /**
     * Mailigen api
     */
    var $api;

    /**
     * Options
     */
    var $options;

    /**
     * Constructor
     */
    function Mailigen_Options() {

        add_action('admin_init', array(&$this, 'init'));
        add_action('admin_menu', array(&$this, 'addOptionsPage'));
        add_action('wp_ajax_reload_fields', array(&$this, 'reloadFieldsList'));
    }

    /**
     * Options form sections configuration
     */
    function sectionsConfig() {

        return array(
            'sect_mg_login' => __('Connect to Mailigen'),
            'sect_mg_lists' => __('Contacts lists'),
            'sect_mg_fields' => __('Fields options')
        );
    }

    /**
     * Options form fields configuration
     */
    function fieldsConfig() {

        return array(
            // login section fields

            'mg_username' => array(
                'id' => 'mg-username',
                'section' => 'sect_mg_login',
                'type' => 'text',
                'title' => __('Mailigen username'),
                'value' => '',
                'desc' => __('Enter Mailigen username.')
            ),
            'mg_password' => array(
                'id' => 'mg-password',
                'section' => 'sect_mg_login',
                'type' => 'password',
                'title' => __('Mailigen password'),
                'value' => '',
                'desc' => __('Enter Mailigen password.')
            ),
            'mg_login' => array(
                'id' => 'mg-login',
                'section' => 'sect_mg_login',
                'type' => 'submit',
                'title' => '&nbsp;',
                'value' => __('Connect to Mailigen')
            ),
            // lists section fields

            'mg_apikey' => array(
                'id' => 'mg-apikey',
                'section' => 'sect_mg_lists',
                'type' => 'text',
                'title' => __('Mailigen API key'),
                'value' => '',
                'desc' => __('Mailigen API key'),
                'readonly' => 'readonly',
            ),
            'mg_fields_list' => array(
                'id' => 'mg-fields-list',
                'section' => 'sect_mg_lists',
                'type' => 'select',
                'title' => __('Contacts list'),
                'value' => '',
                'desc' => __('Your Mailigen contacts list'),
                'choices' => $this->getFieldsLists()
            ),
            // list fields

            'mg_fields' => array(
                'id' => 'mg-fields',
                'section' => 'sect_mg_fields',
                'type' => 'fields',
                'title' => __('Available fields')
            ),
            'mg_fields_buttons' => array(
                'id' => 'mg-fields-buttons',
                'section' => 'sect_mg_fields',
                'type' => 'buttons',
                'title' => '&nbsp;'
            ),
//            'mg_update' => array(
//                'id' => 'mg-update',
//                'section' => 'sect_mg_fields',
//                'type' => 'submit',
//                'title' => '&nbsp;',
//                'value' => __('Save Settings'),
//                'class' => 'button-primary'
//            ),
        );
    }

    function fieldsButtonsConfig() {
        return array(
            'mg_update' => array(
                'id' => 'mg-update',
                'section' => 'sect_mg_fields',
                'type' => 'submit',
                'title' => '&nbsp;',
                'value' => __('Save Settings'),
                'class' => 'button-primary',
            ),
            'mg_remove' => array(
                'id' => 'mg-remove',
                'section' => 'sect_mg_fields',
                'type' => 'submit',
                'title' => '&nbsp;',
                'value' => __('Reset Settings'),
                'class' => 'button-primary',
                'onclick' => 'return confirm("All of the Mailigen settings will be removed!\nAre You sure?");',
            ),
        );
    }

    /**
     * Initialization
     */
    function init() {
        // Get options, if failed return empty array
        $this->options = get_option('mailigen_options') ? get_option('mailigen_options') : array();

        // Start mailigen API
        $this->api = new MGAPI($this->getOption('mg_apikey'));

        // Register settings and validate fields
        register_setting(
                'mailigen_options', 'mailigen_options', array(&$this, 'validate')
        );

        // Add sections to settings
        foreach ($this->sectionsConfig() as $section => $title) {
            add_settings_section(
                    $section, $title, array(&$this, 'showSection'), $section
            );
        }
        // Add fields to sections
        foreach ($this->fieldsConfig() as $id => $field) {
            $field['id'] = $field['id'] ? $field['id'] : $id;
            $field['name'] = $field['name'] ? $field['name'] : $id;
            $this->createField($field);
        }
        // Switch post actions
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            if (isset($_POST['mg_login']))
                $this->login();
            if (isset($_POST['mg_update']))
                $this->update();
            if (isset($_POST['mg_remove']))
                $this->remove();
        }
    }

    /**
     * Add options page to admin menu
     */
    function addOptionsPage() {

        $optionsPage = add_options_page(
                'Mailigen Widget Settings', 'Mailigen Widget', 'manage_options', 'settings-mailigen', array(&$this, 'showPage')
        );
        add_action(
                'load-' . $optionsPage, array(&$this, 'getScripts')
        );
    }

    /**
     * Load scripts (css & js)
     */
    function getScripts() {

        wp_enqueue_style(
                'mg_css', plugins_url('css' . DIRECTORY_SEPARATOR . 'mailigen.css', __FILE__)
        );
        wp_enqueue_script(
                'mg_js', plugins_url('js' . DIRECTORY_SEPARATOR . 'mailigen.js', __FILE__), array('jquery')
        );
    }

    /**
     * Connect and get API key
     */
    function login() {

        $p_username = $this->post('mg_username');
        $p_password = $this->post('mg_password');

        $apikey = $this->api->login($p_username, $p_password);

        if ($this->api->errorCode) {
            $this->showMessage(
                    'login_section', 'login_error', __($this->api->errorMessage), 'error'
            );
            return false;
        }
        $this->options['mg_apikey'] = $apikey;

        update_option(
                'mailigen_options', $this->options
        );
        $this->showMessage(
                'login_section', 'login_success', __('You are logged in Mailigen...'), 'updated'
        );
    }

    /**
     * Update options
     */
    function update() {

        $apikey = $this->post('mg_apikey');
        $listId = $this->post('mg_fields_list');
        $fields = $this->post('mg_fields');

        foreach ($this->getListFieldsVars() as $id => $params) {
            foreach ($fields as $tag => $title) {
                if ($params['tag'] == $tag) {
                    $fields[$tag] = array(
                        $params['name'],
                        $params['field_type'],
                        $params['req']
                    );
                }
            }
        }
        $this->options['mg_apikey'] = $apikey;
        $this->options['mg_fields_list'] = $listId;
        $this->options['mg_fields'] = $fields;

        update_option(
                'mailigen_options', $this->options
        );
        $this->showMessage(
                'options_section', 'update_success', __('Settings saved...'), 'updated'
        );
    }

    /**
     * Get merge vars by list id
     */
    function reloadFieldsList() {

        if ($this->post('mg_list')) {
            $mg_list = $this->post('mg_list');
            unset($this->options['mg_fields']);
            $this->options['mg_fields_list'] = $mg_list;
            update_option('mailigen_options', $this->options);
            do_settings_sections('sect_mg_fields');
        }
        die();
    }

    /**
     * Get option by name
     */
    function getOption($name) {

        return isset($this->options[$name]) ?
                $this->options[$name] : false;
    }

    /**
     * Get merge lists
     */
    function getFieldsLists($lists = array()) {

        if ($this->getOption('mg_apikey')) {
            $retval = $this->api->lists();
            if ($this->api->errorCode)
                return array();
            foreach ($retval as $l)
                $lists[$l['id']] = __($l['name']);
            return $lists;
        } return array();
    }

    /**
     * Get list merge variables
     */
    function getListFieldsVars() {

        if ($lid = $this->getOption('mg_fields_list')) {
            $retval = $this->api->listMergeVars($lid);
            if ($this->api->errorCode)
                return array();
            return $retval;
        } return array();
    }

    /**
     * Validate options form fields
     */
    function validate($field) {

        return $field;
    }

    /**
     * check & prepare post data
     */
    function post($name) {

        $options = 'mailigen_options';

        if (isset($_POST[$name])) {
            return is_array($_POST[$name]) ? $_POST[$name] :
                    esc_attr(stripslashes($_POST[$name]));
        }
        if (isset($_POST[$options][$name])) {
            return is_array($_POST[$options][$name]) ? $_POST[$options][$name] :
                    esc_attr(stripslashes($_POST[$options][$name]));
        }
        return false;
    }

    /**
     * Create options form field
     */
    function createField($args = array()) {

        $defaults = array(
            'id' => 'id',
            'name' => '',
            'section' => 'sect_mg_lists',
            'type' => 'text',
            'title' => __('Default Field'),
            'desc' => __('This is a default description.'),
            'class' => '',
            'value' => '',
            'readonly' => '',
            'choices' => array(),
            'callback' => array(&$this, 'showField')
        );
        extract(wp_parse_args($args, $defaults));

        $fieldArgs = array(
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'desc' => $desc,
            'class' => $class,
            'value' => $value,
            'readonly' => $readonly,
            'choices' => $choices
        );

        add_settings_field(
//                $id, $title, array(&$this, 'showField'), $section, $section, $fieldArgs
                $id, $title, $callback, $section, $section, $fieldArgs
        );
    }

    /**
     * Show options form section
     */
    function showSection() {
        return true;
    }

    /**
     * Show options form field
     */
    function showField($args = array()) {

        extract($args);

        $options = get_option('mailigen_options');
        $class = ( $class != '' ) ? " {$class}" : null;

        switch ($type) {

            // text, password
            case 'text':
            case 'password':
                $readonly = ( $readonly != '' ) ? " readonly='{$readonly}'" : null;
                echo "<input id='{$id}' name='mailigen_options[{$name}]' type='{$type}'  value='{$options[$name]}' class='regular-text{$class}'{$readonly}/>";
                echo ( $desc != '' ) ? "<br /><span class='description'>{$desc}</span>" : "";
                break;

            // submit button
            case 'submit':
                echo "<p class='submit'>";
                echo "<input class='button{$class}' type='{$type}' name='{$name}' value='{$value}' />";
                echo "</p>";
                break;
            // button list
            case 'buttons':
                echo "<div class='buttons'>";
                foreach ($this->fieldsButtonsConfig() as $name => $btnArgs) {
                    $btnArgs['class'] = ( $btnArgs['class'] != '' ) ? " {$btnArgs['class']}" : null;
                    $onsubmit = ( isset($btnArgs['onsubmit']) && $btnArgs['onsubmit'] != '' ) ? " {$btnArgs['onsubmit']}" : null;
                    $onclick = ( isset($btnArgs['onclick']) && $btnArgs['onclick'] != '' ) ? " {$btnArgs['onclick']}" : null;
                    echo "<input class='button{$btnArgs['class']}' type='{$btnArgs['type']}' name='{$name}' value='{$btnArgs['value']}' onclick='{$onclick}' />&nbsp;";
                }
                echo "</div>";
                break;

            // select box
            case 'select':
                echo "<select id='{$id}' class='select{$class}' name='mailigen_options[{$name}]'>";
                echo "<option value=''>-- Select --</option>";
                foreach ($choices as $value => $title) {
                    $value = esc_attr($value);
                    $title = esc_html($title);
                    $selected = ( $options[$name] == $value ) ? ' selected="selected"' : '';
                    echo "<option value='{$value}'{$selected}>{$title}</option>";
                }
                echo "</select>";
                echo ( $desc != '' ) ? "<br /><span class='description'>{$desc}</span>" : "";
                break;

            // merge
            case 'fields':
                echo "<fieldset class='merge'>";
                foreach ($this->getListFieldsVars() as $id => $field) {
                    echo "<label>";
                    $chechked = ( isset($options['mg_fields'][$field['tag']]) || $field['req'] == 1 ) ? 'checked="checked"' : '';
                    if ($field['req'] == 1) {
                        echo "<input type='hidden' name='mailigen_options[mg_fields][{$field['tag']}]' value='{$field['name']}'/>";
                        echo "<input type='checkbox' name='{$field['tag']}' value='{$field['name']}' {$chechked} disabled='disabled' onclick='return false' onkeypress='return false'/>&nbsp;&nbsp;";
                    } else {
                        echo "<input type='checkbox' name='mailigen_options[mg_fields][{$field['tag']}]' value='{$field['name']}' {$chechked}/>&nbsp;&nbsp;";
                    }
                    echo "{$field['name']}<br/>";
                    echo "</label>";
                } echo "</fieldset>";
                break;
        }
    }

    /**
     * Show message
     */
    function showMessage($setting, $code, $message, $type = 'error') {

        add_settings_error(
                $setting, $code, $message, $type
        );
        set_transient(
                'settings_errors', get_settings_errors(), 30
        );
        $goback = add_query_arg(
                'settings-updated', 'true', wp_get_referer()
        );
        wp_redirect($goback);
        die();
    }

    /**
     * Show options page
     */
    function showPage() {

        settings_fields('mailigen_options');

        // wrapper
        echo "<div class='wrap'>";

        // header
        echo "<div class='icon32' id='icon-options-general'></div>";
        echo "<h2>" . __('Mailigen settings ') . "</h2>";

        if ($this->getOption('mg_apikey')) {

            // options form
            echo "<form id='mg-options-form' action='' method='post' name='mg_options_form'>";
            echo do_settings_sections('sect_mg_lists');

            // fields list
            $display = $this->getOption('mg_fields_list') ? 'block' : 'none';
            echo "<div id='mg-fields-container' style='display:{$display}'>";
            echo do_settings_sections('sect_mg_fields');
            echo '<div class="action-buttons">';

            echo '</div>';
            echo "</div>";

            // EOF options form
            echo "</form>";
        } else {

            // login form
            echo "<form id='mg-login-form' action='' method='post' name='mg_login_form'>";
            echo do_settings_sections('sect_mg_login');
            echo "</form>";
        }

        // EOF wrapper
        echo "</div>";
    }

    /**
     * Remove mailigen settings
     */
    function remove() {
        delete_option('mailigen_options');
        $goback = add_query_arg(
                'settings-updated', 'true', wp_get_referer()
        );
        wp_redirect($goback);
    }

}

if (is_admin())
    new Mailigen_Options();

function pr($array, $die = false) {

    echo "<pre>";
    print_r($array);
    echo "</pre>";
    if ($die)
        die();
}

?>