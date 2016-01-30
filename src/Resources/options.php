<?php

class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Contact Form', 
            'Contact Form', 
            'manage_options', 
            'egig_contact_form', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'egig_contact_form' );
        ?>
        <div class="wrap">
            <h2>Contact Form</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'egig_contact_form' );   
                do_settings_sections( 'egig_contact_form' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'egig_contact_form', // Option group
            'egig_contact_form', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_smtp', // ID
            'SMTP Server', // Title
            array( $this, 'smtp_section_info' ), // Callback
            'egig_contact_form' // Page
        );

        add_settings_field(
            'smtp_host', // ID
            'SMTP Host', // Title 
            array( $this, 'smtp_host_callback' ), // Callback
            'egig_contact_form', // Page
            'setting_section_smtp' // Section           
        );

        add_settings_field(
            'smtp_port', // ID
            'SMTP Port', // Title 
            array( $this, 'smtp_port_callback' ), // Callback
            'egig_contact_form', // Page
            'setting_section_smtp' // Section           
        );

        add_settings_field(
            'smtp_user', 
            'SMTP User', 
            array( $this, 'smtp_user_callback' ), 
            'egig_contact_form', 
            'setting_section_smtp'
        );

         add_settings_field(
            'smtp_pass', 
            'SMTP Password', 
            array( $this, 'smtp_pass_callback' ), 
            'egig_contact_form', 
            'setting_section_smtp'
        );

         add_settings_field(
            'smtp_ssl', 
            'SMTP SSL', 
            array( $this, 'smtp_ssl_callback' ), 
            'egig_contact_form', 
            'setting_section_smtp'
        );

        add_settings_section(
            'setting_section_recaptcha', // ID
            'Google Recatcha', // Title
            array( $this, 'recaptcha_section_info' ), // Callback
            'egig_contact_form' // Page
        );

        add_settings_field(
            'recaptcha_secret', 
            'Secret Key', 
            array( $this, 'recaptcha_secret_callback' ), 
            'egig_contact_form', 
            'setting_section_recaptcha'
        );

        add_settings_section(
            'setting_section_mailto', // ID
            'Mail Destination', // Title
            array( $this, 'mailto_section_info' ), // Callback
            'egig_contact_form' // Page
        );

        add_settings_field(
            'mail_from_name', 
            'Mail From Name', 
            array( $this, 'mail_from_name_callback' ), 
            'egig_contact_form', 
            'setting_section_mailto'
        );

        add_settings_field(
            'mail_to', 
            'Mail to', 
            array( $this, 'mail_to_callback' ), 
            'egig_contact_form', 
            'setting_section_mailto'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['smtp_port'] ) )
            $new_input['smtp_port'] = absint( $input['smtp_port'] );

        if( isset( $input['smtp_host'] ) )
            $new_input['smtp_host'] = sanitize_text_field( $input['smtp_host'] );

        if( isset( $input['smtp_user'] ) )
            $new_input['smtp_user'] = sanitize_text_field( $input['smtp_user'] );

        if( isset( $input['smtp_pass'] ) )
            $new_input['smtp_pass'] = $input['smtp_pass'];

        $new_input['smtp_ssl'] = $input['smtp_ssl'];

        if( isset( $input['recaptcha_secret'] ) )
            $new_input['recaptcha_secret'] = sanitize_text_field( $input['recaptcha_secret'] );

        if( isset( $input['mail_to'] ) )
            $new_input['mail_to'] = sanitize_text_field( $input['mail_to'] );

        if( isset( $input['mail_from_name'] ) )
            $new_input['mail_from_name'] = sanitize_text_field( $input['mail_from_name'] );
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function smtp_section_info()
    {
        print 'Enter your SMTP Server settings below:';
    }

    public function recaptcha_section_info()
    {
        print 'Enter your your google recaptcha  secret key below:';
    }

    public function mailto_section_info()
    {
        print 'Enter destination email(s) you want to message sent to (separated by comma)';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function smtp_host_callback()
    {
        printf(
            '<input type="text" id="smtp_host" name="egig_contact_form[smtp_host]" value="%s" />',
            isset( $this->options['smtp_host'] ) ? esc_attr( $this->options['smtp_host']) : ''
        );
    }

    public function smtp_port_callback()
    {
        printf(
            '<input type="text" id="smtp_port" name="egig_contact_form[smtp_port]" value="%s" />',
            isset( $this->options['smtp_port'] ) ? esc_attr( $this->options['smtp_port']) : ''
        );
    }

    public function smtp_user_callback()
    {
        printf(
            '<input type="text" id="smtp_user" name="egig_contact_form[smtp_user]" value="%s" />',
            isset( $this->options['smtp_user'] ) ? esc_attr( $this->options['smtp_user']) : ''
        );
    }

    public function smtp_pass_callback()
    {
        printf(
            '<input type="password" id="smtp_pass" name="egig_contact_form[smtp_pass]" value="%s" />',
            isset( $this->options['smtp_pass'] ) ? esc_attr( $this->options['smtp_pass']) : ''
        );
    }

    public function smtp_ssl_callback()
    {
        printf(
            '<input type="checkbox" id="smtp_ssl" name="egig_contact_form[smtp_ssl]" value="1" %s/>',
            ( $this->options['smtp_ssl'] == 1 ) ? 'checked="checked"' : ''
        );
    }

    public function recaptcha_secret_callback()
    {
        printf(
            '<input type="text" id="recaptcha_secret" name="egig_contact_form[recaptcha_secret]" value="%s" />',
            isset( $this->options['recaptcha_secret'] ) ? esc_attr( $this->options['recaptcha_secret']) : ''
        );
    }

    public function mail_to_callback()
    {
        printf(
            '<textarea name="egig_contact_form[mail_to]" />%s</textarea>',
            isset( $this->options['mail_to'] ) ? esc_attr( $this->options['mail_to']) : ''
        );
    }

    public function mail_from_name_callback()
    {
        printf(
            '<input type="text" name="egig_contact_form[mail_from_name]" value="%s" />',
            isset( $this->options['mail_from_name'] ) ? esc_attr( $this->options['mail_from_name']) : ''
        );
    }
}

if( is_admin() ) {
    $my_settings_page = new MySettingsPage();
}