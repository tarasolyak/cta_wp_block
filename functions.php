<?php

add_action( 'wp_enqueue_scripts', 'st_name_scripts' );

function st_name_scripts() {
    wp_enqueue_style( 'style', get_stylesheet_directory_uri() . "/style.css" );
    wp_enqueue_script( 'script', get_stylesheet_directory_uri() . "/assets/js/script.js", array( 'jquery' ), '', true);
    wp_localize_script(
        "script", 
        "ajaxurl", 
        array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
}

add_action( 'wp_ajax_leadpost', 'leadpost' );
add_action( 'wp_ajax_nopriv_leadpost', 'leadpost' );

function leadpost() {
    // required

    $response = array();
    $response['status'] = 'error';

    if (empty($_POST["name"]) || !isset($_POST["name"])) {
        $response['error_name_field'] = true;
        wp_send_json($response);
        return;
    }

    if (empty($_POST["phone"]) || !isset($_POST["phone"])) {
        $response['error_email_field'] = true;
        wp_send_json($response);
        return;
    }

    $code = !empty($_POST["code"]) ? $_POST["code"] : "n/a ";
    
    // main field
    $name = $_POST["name"];
    $phone = $code . $_POST["phone"];
    

    // other fields
    $email = !empty($_POST["mail"]) ? $_POST["mail"] : "Невказано";
    $message = !empty($_POST["message"]) ? htmlspecialchars($_POST["message"]) : "Невказано";
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "Ip не отримано" ;
    $date_order = date('Y-m-d H:i:s');

    // utm field
    $ref = $_SERVER['HTTP_REFERER'];
    $utm = '';

    if($ref){
        $urlParts = parse_url($ref);
        if(isset($urlParts['query'])){

            parse_str($urlParts['query'], $params);

            $utm .= isset($params["utm_source"]) ? "source: " . $params["utm_source"] . "<br>" : '';
            $utm .= isset($params["utm_medium"]) ? "medium: " . $params["utm_medium"] . "<br>" : '';
            $utm .= isset($params["utm_campaign"]) ? "campaign: " . $params["utm_campaign"] . "<br>" : '';
            $utm .= isset($params["utm_term"]) ? "term: " . $params["utm_term"] . "<br>" : '';
            $utm .= isset($params["utm_content"]) ? "content: " . $params["utm_content"] . "<br>" : '';
    
        }
    } else {
        $utm = "Параметри не отримано.";
    }


    // send email
    $to = get_option("admin_email");
    $subject = "Нове замовлення";
    $body = "<b>Нове замовлення.</b><br>
    <p>Ім'я: $name</p>
    <p>Номер телефону: $phone</p>
    <p>Пошта: $email</p>
    <p>Повідомлення: $message</p>
    <p>---</p>
    <p>Ip: $ip</p>
    <p>Дата: $date_order</p>
    <p>Utm мітки: $utm</p>
    ";
    // $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: Wordpress<wordpress@mysite.com>';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    // якщо поле не вказане відправка буде на email адрес адміністратора
    $cta_emails = get_option("cta_emails");

    if ($cta_emails[0] !== '' && !empty($cta_emails)) {
        $to = implode(', ', $cta_emails);
    }

    // send data to email
    $sent_message = wp_mail( $to, $subject, $body, $headers );

    // send post to admin
    $new_post = array(
    'post_title' => $name,
    'post_content' => $message ? $message : '',
    'post_status' => 'draft',
    'post_date' => date('Y-m-d H:i:s'),
    'post_type' => 'lead',
    'post_category' => array(0)
    );
    $post_id = wp_insert_post($new_post);
    
    update_post_meta($post_id, "cta_phone", $phone);
    update_post_meta($post_id, "cta_email", $email);
    update_post_meta($post_id, "_cta_date", $date_order);
    update_post_meta($post_id, "_cta_ip", $ip);
    update_post_meta($post_id, "_cta_utm", $utm);

    if ( $sent_message ) {
        $response['status'] = 'success';
    } else {
        $response['notice'] = 'Помилка відправки повідомлення';
    }

    wp_send_json($response);
    wp_die();
}

function create_post_leads() {

        $labels = array(
            'name'               => 'Leads',
            'singular_name'      => 'Lead',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Lead',
            'edit_item'          => 'Edit Lead',
            'new_item'           => 'New lead',
            'all_items'          => 'All Leads',
            'view_item'          => 'View Lead',
            'search_items'       => 'Search Leads',
            'not_found'          => 'No leads found',
            'not_found_in_trash' => 'No leads found in the Trash',
            'menu_name'          => 'Leads'
        );
    
        $args = array(
            'labels'        => $labels,
            'description'   => 'Замовлення від форми',
            'public'        => true,
            'menu_position' => 5,
            'supports'      => array( 'title', 'editor', "custom-fields" ),
            'has_archive'   => true,
        );
    
        register_post_type( 'lead', $args ); 
      }
    
    add_action( 'init', 'create_post_leads' );

add_filter( 'manage_lead_posts_columns', 'cta_filter_posts_columns' );

function cta_filter_posts_columns( $columns ) {

    $columns = array(
        'cb' => $columns['cb'],
        'title' => "Ім'я",
        'phone' => "Телефон",
        'email' => "Email",
        'text' => "Текст",
        'utm' => "Мітки",
        'ip' => "Ip",
        'date_order' => "Час отримання"
    );

    return $columns;
}

add_action( 'manage_lead_posts_custom_column', 'cta_realestate_column', 10, 2);

function cta_realestate_column( $column, $post_id ) {

  if ( 'text' === $column ) {
    $content_post = get_post($post_id);
    $content = $content_post->post_content;
    $cut_content = (strlen($content) > 60) ? substr($content, 0, 40) . '...' : $content;
    echo $cut_content;
  }
  if ( 'phone' === $column ) {
    $phone = get_post_meta( $post_id, 'cta_phone', true );
    echo $phone;
  }
  if ( 'email' === $column ) {
    $email = get_post_meta( $post_id, 'cta_email', true );
    echo $email;
  }
  if ( 'ip' === $column ) {
    $ip = get_post_meta( $post_id, '_cta_ip', true );
    echo $ip;
  }
  if ( 'utm' === $column ) {
    $utm = get_post_meta( $post_id, '_cta_utm', true );
    echo $utm;
  }
  if ( 'date_order' === $column ) {
    $date_order = get_post_meta( $post_id, '_cta_date', true );
    echo $date_order;
  }
}

/**
 * Adds a submenu page under a custom post type parent.
 */
add_action( 'admin_menu', 'lead_register_sub_page' );

function lead_register_sub_page() {
    add_submenu_page(
        'edit.php?post_type=lead',
        'Налаштування',
        'Налаштування повідомлень',
        'manage_options',
        'settings_lead',
        'leads_ref_page_callback'
    );
}

/** 
 * Display callback for the submenu page.
 */

function leads_ref_page_callback() {
    ?>
    <div class="wrap">
        <h1>Відправка замовлень</h1>
        <form action="options.php" method="POST">
            <?php settings_fields('cta_email_blocks') ?>
 
                <?php do_settings_sections('settings_lead') ?>

            <?php submit_button() ?>
        </form>

        <script>
            const add_field = document.querySelector(".add_field");
            const wrap_field = document.querySelector(".cta_wrap_field");

            function add_new_field() {
                                
                add_field.addEventListener('click', () => { 
                    let cta_emails = document.querySelectorAll(".cta_emails");
                    wrap_field.insertAdjacentHTML("beforeEnd", `<p class='nt_def'><input type='email' name='cta_emails[]' placeholder='email@gmail.com' required class='regular-text cta_emails field_${cta_emails.length}'><span class='delete_field cta_link ml-5'>Видалити email</span></p>`);
                    remove_field();
                });
            }

            function remove_field() {
                del_field = document.querySelectorAll(".nt_def");

                del_field.forEach((elem, index) => {
                    elem.addEventListener('click', (e) => {
                        if (e.target.tagName === "SPAN") {
                            elem.remove();
                        }
                    })
                })
            }

            add_new_field();
            remove_field();

        </script>

    </div>
    <?php
}

add_action("admin_init", "cta_block_settings");

function cta_block_settings() {
    register_setting("cta_email_blocks", "cta_emails");
    register_setting("cta_email_blocks", "cta_telegram_token");
    register_setting("cta_email_blocks", "cta_google_sheets_token");

    add_settings_section("cta_email_section", "Налаштування відправки", "cta_foo_email_section", "settings_lead");

    add_settings_field("cta_email_field", "E-mail", "cta_foo_email_field", "settings_lead", "cta_email_section");

    add_settings_field("cta_telegram_field", "Telegram token", "cta_foo_telegram_field", "settings_lead", "cta_email_section");

    add_settings_field("cta_googlesheets_field", "Google Sheets token", "cta_foo_google_field", "settings_lead", "cta_email_section");
}


function cta_foo_email_section() {
    echo "<p>Якщо поле не вказане, відправка буде на email адрес адміністратора.</p>";
}

function cta_foo_email_field() {
    $cta_emails = get_option("cta_emails");

    // var_dump($cta_emails);
    $id_field = 0;
    echo "<style>
    .cta_link {
        color: #2271b1;
        transition-property: border,background,color;
        transition-duration: .05s;
        transition-timing-function: ease-in-out;
        cursor: pointer;
        text-decoration: underline;
        display: contents;
    }
    .ml-5 {
        margin-left: 5px;
    }
    </style>";
    echo "<div class='cta_wrap_field' style='display: flex;flex-direction: column;gap: 5px;'>
    <span class='add_field cta_link'>Додати нове поле для email</span>";

    foreach($cta_emails as $cta_email) {
        if ($id_field == 0) {
            echo "<p><input type='email' name='cta_emails[]' placeholder='email@gmail.com' class='regular-text cta_emails field_$id_field' value='" . $cta_email . "'></p>";
        } else {
            echo "<p class='nt_def'><input type='email' placeholder='email@gmail.com' required name='cta_emails[]' class='regular-text cta_emails field_$id_field' value='" . $cta_email . "'><span class='delete_field cta_link ml-5'>Видалити email</span></p>";
        }
        
        $id_field += 1;
    }

    echo "</div>";
}

function cta_foo_telegram_field() {
    $cta_telegram = get_option("cta_telegram_token");

    echo "<p><input type='text' placeholder='token' name='cta_telegram_token' class='regular-text' value='" . $cta_telegram . "'></p>";
}

function cta_foo_google_field() {
    $cta_google = get_option("cta_google_sheets_token");

    echo "<p><input type='text' placeholder='token' name='cta_google_sheets_token' class='regular-text' value='" . $cta_google . "'></p>";
}
