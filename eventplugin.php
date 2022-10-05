<?php
/*
 * Plugin Name: eventplugin
 * Description:  собственно плагин, первый, кривой-косой, но плагин для WP; нужен для работы с событиями;
 * и я пока ума не приложу, как и что он будет делать :-)
 * Version: 0.1
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 8.0
*/

function create_taxonomies()
{

    // Add a taxonomy like categories
    $labels = array(
        'name' => 'Types',
        'singular_name' => 'Type',
        'search_items' => 'Search Types',
        'all_items' => 'All Types',
        'parent_item' => 'Parent Type',
        'parent_item_colon' => 'Parent Type:',
        'edit_item' => 'Edit Type',
        'update_item' => 'Update Type',
        'add_new_item' => 'Add New Type',
        'new_item_name' => 'New Type Name',
        'menu_name' => 'Types',
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


function eventplugin_activate()
{
    $labels = array(
        'name' => 'Events',
        'singular_name' => 'Events',
        'menu_name' => 'Events',
        'name_admin_bar' => 'Event',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Event',
        'new_item' => 'New Event',
        'edit_item' => 'Edit Event',
        'view_item' => 'View Event',
        'all_items' => 'All Events',
        'search_items' => 'Search Events',
        'parent_item_colon' => 'Parent Events',
        'not_found' => 'No Events Found',
        'not_found_in_trash' => 'No Events Found in Trash'
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
        'supports' => array('title', 'status', 'eventdate'),

        //'custom-fields',	, 'custom-fields','editor', 'author', 'thumbnail', 'excerpt', 'comments'

        'has_archive' => true,
        'rewrite' => array('slug' => 'events'),
        'query_var' => true
    );


    register_post_type('events', $args);//регистрирую кастомный контент тайп

    // Сбрасываем настройки ЧПУ, чтобы они пересоздались с новыми данными
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'eventplugin_install');
register_deactivation_hook(__FILE__, 'eventplugin_deactivate');


add_action('add_meta_boxes', 'my_extra_fields', 1);//кастомные поля

add_action('init', 'create_taxonomies');//таксономия
add_action('init', 'eventplugin_activate');//инициализация кастомных контент тайпов


function eventplugin_install()
{
    //тут делаю что-то полезное

}


function eventplugin_deactivate()
{ //тут удаляю контент тайп
    unregister_post_type('events');
    //и надо чистить БД от данных виджета
    unregister_widget('event_widget');

}



/*
 * add_action( 'init', 'post_tag_for_pages' );
function post_tag_for_pages(){
	register_taxonomy_for_object_type( 'post_tag', 'page');
}
*/


//ПРОИЗВОЛЬНЫЕ ПОЛЯ

// подключаем функцию активации мета блока (my_extra_fields)


function my_extra_fields()
{
    add_meta_box('extra_fields', 'Новые поля', 'extra_fields_box_func', 'events', 'normal', 'high');
}


// код блока
function extra_fields_box_func($post)
{
    ?>

    <p>Статус: <?php $mark_v = get_post_meta($post->ID, 'status', 1); ?>

        <label><input type="radio" name="extra[status]" value="open" <?php checked($mark_v, 'open'); ?> /> open</label>
        <label><input type="radio" name="extra[status]" value="closed" <?php checked($mark_v, 'closed'); ?> />
            closed</label>

    </p>

    <p>
        Дата: <?php $eventDate = get_post_meta($post->ID, 'eventdate', 1); ?>
        <input type='date' name="extra[eventdate]" value=<?= $eventDate ?>/>
    </p>


    <input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>"/>
    <?php
}


// включаем обновление полей при сохранении
add_action('save_post', 'my_extra_fields_update', 0);

## Сохраняем данные, при сохранении поста
function my_extra_fields_update($post_id)
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


add_filter('manage_events_posts_columns', function ($columns) {

    $my_columns = [
        'status' => 'Состояние события',
        'eventdate' => 'Дата события',
    ];
    //   var_dump($my_columns);
    array_pop($columns);//удаляю дату создания записи о событии
    return $columns + $my_columns;

    //array_slice( $columns, 0, 1 ) + $my_columns + $columns;
});


// Выводим контент для каждой из зарегистрированных нами колонок. Обязательно.
add_action('manage_events_posts_custom_column', function ($column_name) {

    $custom_fields = get_post_custom();
    $my_custom_field = $custom_fields[$column_name];
    echo($my_custom_field[0]);

    /*if ( $column_name === 'status' ) {
        $custom_fields = get_post_custom();
        $my_custom_field = $custom_fields['status'];
        echo ( $my_custom_field[0]);

    }

    if ( $column_name === 'eventdate' ) {
        $custom_fields = get_post_custom();
  $my_custom_field = $custom_fields['eventdate'];

        echo ( $my_custom_field[0]);

    }
*/

});

// Добавляем стили для зарегистрированных колонок. Необязательно.
add_action('admin_print_footer_scripts-edit.php', function () {
    ?>
    <style>
        .column-id {
            width: 50px;
        }

        .column-thumb img {
            max-width: 100%;
            height: auto;
        }
    </style>
    <?php
});


class event_widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
            'event_widget',// widget ID
            __('Визжит событий :-)', ' event_widget_domain'),// widget name
            array('description' => __('Виджет событий для WordPress', 'event_widget_domain'),)// widget description
        );
    }

    public function widget($args, $instance)//внешний вид для вывода на фронт!
    {
        ?>
        <div style="width:200px;height:150px;border:5px solid lime;"> <?php
            $numberofevents = $instance['numberofevents'];//считал значение числа событий, введённое в админке в виджет
            $typeofevents = $instance['typeofevents'];//ну и вид события оттуда же

            //  $numberofevents= apply_filters('widget_numberofevents', $instance['numberofevents']);
           // $typeofevents = apply_filters('widget_typeofevents', $instance['typeofevents']);

            echo $args['before_widget'];
            //if title is present
           // if (!empty($eventdate))
            echo $args['before_title'] . "СОБЫТИЯ".$args['after_title'];
            //output
           // var_dump($instance);
            echo($numberofevents.'***'.$typeofevents); echo('<br><br>');
         //   var_dump($args);

       // $args = array( 'post_type' => 'events' );//, 'posts_per_page' => -1

            $args2 =   array(
                'post_type' => 'events',
             //   'meta_key' => 'eventdate',
               // 'meta_key' => 'status',
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => $typeofevents,//ищу  события по статусу
                    ),
                    array(
                        'key' => 'eventdate',
                        'value' => '2022-01-01',
                        'compare' => '>=',
                        'type' => 'DATE',
                    ),
                ),
            );

        $loop = new WP_Query( $args2 );




        while ( $loop->have_posts() ) : $loop->the_post();


            ?>
            <li><?php the_title(); echo "  "; echo (get_post_custom_values('status')[0]);
                echo "  "; echo (get_post_custom_values('eventdate')[0]);
            ?></li>
            <?php
        // echo esc_attr($loop->post->post_title);
        echo('<br>');
       endwhile;

            wp_reset_postdata();
           // echo __('echoed text from plugin', 'event_widget_domain');
            echo $args['after_widget'];
            ?>
        </div>
        <?php

        /*

            register_sidebar( array(
               'name' => __( 'Телефон в шапке', '' ),
               'id' => 'top-area',
               'description' => __( 'Шапка', '' ),
               'before_widget' => '',
               'after_widget' => '',
               'before_title' => '<h3>',
               'after_title' => '</h3>',
           ) );

       */
//<div class="top_phone">
        //<?php dynamic_sidebar( 'top-area' );
//</div>


    }


    public function form($instance)//внешний вид для заполнения виджета в админке!
    {

        if (isset($instance['numberofevents']))
        {$numberofevents = $instance['numberofevents'];};
       // else
         //   $numberofevents = 0;//число событий для отображения в виджете!

        if (isset($instance['typeofevents']))
        {  $typeofevents = $instance['typeofevents'];};
        //else
          //  $typeofevents = 'open';//статус отображаемых событий в виджете! [ open / closed ]
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('numberofevents'); ?>"><?php _e('Number of events:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('numberofevents'); ?>"
                   name="<?php echo $this->get_field_name('numberofevents'); ?>" type="text"
                   value="<?php echo esc_attr($numberofevents); ?>"/>
        </p>

        <p>Статус: <?php
            $v = $this->get_field_id('typeofevents');
            echo $typeofevents; ?>

            <label><input type="radio" name="<?php echo $this->get_field_name('typeofevents'); ?>"
                          value="open" <?php checked($v, 'open'); ?> /> open</label>

            <label><input type="radio" name="<?php echo $this->get_field_name('typeofevents'); ?>"
                          value="closed" <?php checked($v, 'closed'); ?> /> closed</label>
        </p>

        <?php
    }

    /* public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }*/

    function update($new_instance, $old_instance)
    {

        $instance = $old_instance;
        $instance['typeofevents'] = (!empty($new_instance['typeofevents'])) ? strip_tags($new_instance['typeofevents']) : '';
        $instance['numberofevents'] = (!empty($new_instance['numberofevents'])) ? strip_tags($new_instance['numberofevents']) : '';

        //$instance[ 'depth' ] = strip_tags( $new_instance[ 'depth' ] );
        return $instance;

    }
}

function event_register_widget()
{
    register_widget('event_widget');
}

add_action('widgets_init', 'event_register_widget');