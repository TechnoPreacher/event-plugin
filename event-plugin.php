<?php
/*
 * Plugin Name: Событийный плагин :-)
 * Description:  Позволяет создавать виджет с предстоящими событиями; события могут содержать таксономии; имеется шорткод
 * Version: 1.1
 * Text Domain: event-plugin
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
*/


//===ЦЕПЛЯЮ кастом филдс, таксономию, виджет, шорткод, и возможность удаления к событиям ядра===
add_action('plugins_loaded', 'event_plugin_loaded');//подключаем переводчик
add_action('add_meta_boxes', 'my_extra_fields', 1);//кастомные поля
add_action('init', 'create_taxonomies');//таксономия
add_action('init', 'create_custom_content_type');//инициализация кастомных контент тайпов
add_action('save_post', 'my_extra_fields_update', 0); // включаем обновление полей при сохранении
add_action('widgets_init', 'event_register_widget');//прикручиваю виджет
add_shortcode('events', 'event_shortcode');//прикручиваю шорткод
register_deactivation_hook(__FILE__, 'event_plugin_deactivate');//убираю всё что сделал плагин
//===============================================================================================


include_once __DIR__ . '/includes/event-widget.php';// Include WP_widget child class

function event_plugin_loaded()
{
    $text_domain_dir = dirname(plugin_basename(__FILE__)) . '/lang/';
    load_plugin_textdomain('event-plugin', false, $text_domain_dir);
}


function event_plugin_deactivate()
{
    unregister_post_type('events');//тут удаляю контент тайп
    unregister_widget('event_widget');//убить виджет
    remove_shortcode('events');//убить шорткод
    //TODO: чистить БД от данных ...
}

function create_taxonomies()//таксономия
{
    $labels = array(
        'name' => __('Таксономия ивента', 'event-plugin'),
        'singular_name' => __('Type', 'event-plugin'),
        'search_items' => __('Search Types', 'event-plugin'),
        'all_items' => __('All Types', 'event-plugin'),
        'parent_item' => __('Родитель', 'event-plugin'),

        'parent_item_colon' => __('Parent Type:', 'event-plugin'),
        'edit_item' => __('Редактировать таксономию ;-)', 'event-plugin'),
        'update_item' => __('Обновить таксономию ;-)', 'event-plugin'),
        'add_new_item' => __('Добавить новую таксономию ;-)', 'event-plugin'),
        'new_item_name' => __('Новая таксономия ;-)', 'event-plugin'),
        'menu_name' => __('Таксономия :-)', 'event-plugin'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'type'),
    );
    register_taxonomy('events_type', array('events'), $args);
}

function create_custom_content_type()
{
    $labels = array(
        'name' => __('События :-)', 'event-plugin'),
        'singular_name' => 'События :-)',
        'menu_name' => __('События :-)', 'event-plugin'),
        'name_admin_bar' => 'Event',
        'add_new' => 'Добавить...',
        'add_new_item' => 'Добавление события :-)',
        'new_item' => 'Новое событие :-)',
        'edit_item' => 'Редактировать  :-)',
        'view_item' => 'View Event',
        'all_items' => 'Все',
        'search_items' => 'Search Events',
        'parent_item_colon' => 'Parent Events',
        'not_found' => 'Пока что событий нет :-(',
        'not_found_in_trash' => 'Корзина пуста!'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-admin-appearance',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'status', 'eventdate'),//, 'editor'
        'has_archive' => true,
        'rewrite' => array('slug' => 'events'),
        'query_var' => true
    );
    register_post_type('events', $args);//регистрирую кастомный контент тайп

    flush_rewrite_rules();//!!! Сбрасываем настройки ЧПУ, чтобы они пересоздались с новыми данными
}

// подключаем функцию активации мета блока (my_extra_fields) - нужно для вывода кастомных полей на странице "добавить" в админке!
function my_extra_fields()
{
    add_meta_box('extra_fields', 'Поля ивента', 'extra_fields_box_func', 'events', 'normal', 'high');
}

function extra_fields_box_func($post)// код блока (внешний вид на странице добавления события в админке)
{
    ?>
    <div style="width:100%;height:100%;border:5px solid orangered;">

        <p>Статус ивента: <?php $mark_v = get_post_meta($post->ID, 'status', 1); ?>
            <label>
                <input type="radio" name="extra[status]"
                       value="open" <?php checked($mark_v, 'open'); ?> /> open
            </label>
            <label>
                <input type="radio" name="extra[status]"
                       value="closed" <?php checked($mark_v, 'closed'); ?> />closed
            </label>
        </p>

        <p>Дата ивента: <?php $eventDate = get_post_meta($post->ID, 'eventdate', 1); ?>
            <input type='date' name="extra[eventdate]"
                   value="<?= $eventDate ?>"/>
        </p>

        <input type="hidden" name="extra_fields_nonce"
               value="<?php echo wp_create_nonce(__FILE__); ?>"/>
    </div>
    <?php
}

function my_extra_fields_update($post_id)//Сохрание маета-данных, при сохранении поста
{
    // базовая проверка
    if (
        empty($_POST['extra'])
        || !wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__)
        || wp_is_post_autosave($post_id)
        || wp_is_post_revision($post_id)
    )
        return false;
    // Все ОК! Теперь, нужно сохранить/удалить данные
    $_POST['extra'] = array_map('sanitize_text_field', $_POST['extra']); // чистим все данные от пробелов по краям
    foreach ($_POST['extra'] as $key => $value) {
        if (empty($value)) {
            delete_post_meta($post_id, $key); // удаляем поле если значение пустое
            continue;
        }
        update_post_meta($post_id, $key, $value); // add_post_meta() работает автоматически
    }
    return $post_id;
}

add_filter('manage_events_posts_columns', function ($columns) {//вывод значений мета-полей в общем списке в админке!
    $my_columns = [
        'status' => 'Состояние события',
        'eventdate' => 'Дата события',
    ];
    array_pop($columns);//удаляю дату создания записи о событии, для меня важнее метадата самого события!
    return $columns + $my_columns;
});


add_action('manage_events_posts_custom_column', function ($column_name) {// Выводим контент (значение) для каждой из зарегистрированных колонок $column_name в списке событий в админке
    $custom_fields = get_post_custom();
    $my_custom_field = $custom_fields[$column_name];
    $color = '';//цвет
    if ($my_custom_field[0] == 'open') {
        $color = 'green';
    };
    if ($my_custom_field[0] == 'closed') {
        $color = 'red';
    };
    echo("  <p style=\"color:$color;\"> $my_custom_field[0] </p>");
});


function event_register_widget()
{
    register_widget('event_widget');
}

function event_shortcode($atts)
{
    $atts = shortcode_atts([
        'numbers' => '0', 'status' => 'open',
    ], $atts);

    $dateNow = date_create('now');
    $dateNow = date_format($dateNow, "Y-m-d");
    $num = $atts['numbers'];
    $st = $atts["status"];

    $args2 = array(
        'post_type' => 'events',
        'posts_per_page' => $num,
        'meta_key' => 'eventdate',
        'meta_query' => array(
            array(
                'key' => 'status',
                'value' => $st,//ищу  события по статусу
            ),

            'eventdate_clause' => array(
                'key' => 'eventdate',
                'value' => $dateNow,
                'compare' => '>=',
                'type' => 'DATE',
            ),
        ),

        'orderby' => array(
            'eventdate_clause' => 'ASC',
        ),
    );

    $loop = new WP_Query($args2);

    $htmlLoopOutput = ""; //тут html для
    while ($loop->have_posts()) : $loop->the_post();
        $t = the_title('', '', false);
        $d = (get_post_custom_values('eventdate')[0]);
        $htmlLoopOutput .= "<li> $t $d</li> <br> ";
    endwhile;

    wp_reset_postdata();

    return " 
 <div style=\"width:100%;height:100%;border:4px solid orangered;\"> 
    <p> ВСЕГО $num событий $st типа с датой позже $dateNow </p>
    <p> $htmlLoopOutput </p>
 </div>
 ";
}