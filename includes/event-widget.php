<?php

require_once 'Arguments_For_Loop.php';

class event_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'event_widget',// widget ID
            __('Визжит событий :-)', 'event-plugin'),// widget name
            array('description' => __('Виджет событий для WordPress', 'event-plugin'),)// widget description
        );
    }

    public function widget($args, $instance)//внешний вид для вывода на фронт!
    {
        ?>

        <div style="width:100%;height:100%;border:5px solid yellow;"> <?php
            $numberofevents = $instance['numberofevents'];//считал значение числа событий, введённое в админке в виджет
            $typeofevents = $instance['typeofevents'];//ну и вид события оттуда же

            _e('СОБЫТИЯ', 'event-plugin');
            _e(' ','event-plugin');
            _e("[$numberofevents штук $typeofevents типа]",'event-plugin');


            $loop = new WP_Query( Arguments_For_Loop::arguments($numberofevents,$typeofevents));

            echo "<table style = \"  border-collapse: collapse;\" >";

            while ($loop->have_posts()) : $loop->the_post(); ?>
                <tr>
                    <td style=" border: 1px solid black">
                        <?php the_title(); ?>
                    </td>
                    <td style=" border: 1px solid black">
                        <?php echo(get_post_custom_values('eventdate')[0]); ?>
                    </td>
                </tr>

            <?php
            endwhile;
            echo "</table>";
            wp_reset_postdata();
            echo $args['after_widget'];
            ?>
        </div>
        <?php
    }


    public function form($instance)//внешний вид для заполнения виджета в админке!
    {


        $numberofevents = (isset($instance['numberofevents'])) ? $instance['numberofevents'] : 1;
        $typeofevents = (isset($instance['typeofevents'])) ? $instance['typeofevents'] : 'open';


     /*   if (isset($instance['numberofevents'])) {
            $numberofevents = $instance['numberofevents'];
        } else
            $numberofevents = 1;//число событий для отображения в виджете на старте!

        if (isset($instance['typeofevents'])) {
            $typeofevents = $instance['typeofevents'];
        } else
            $typeofevents = 'open';//статус отображаемых событий в виджете! [ open / closed ]
      */  ?>


        <p>
            <label for="<?php echo $this->get_field_id('numberofevents'); ?>"><?php _e('Number of events:','event-plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('numberofevents'); ?>"
                   name="<?php echo $this->get_field_name('numberofevents'); ?>" type="text"
                   value="<?php echo esc_attr($numberofevents); ?>"/>
        </p>

        <p>Статус для событий в виджете: <?php
            $v = $this->get_field_name('typeofevents');
            ?>

            <label><input type="radio" name="<?php echo $v; ?>"
                          value="open" <?php checked($typeofevents, 'open'); ?> /> open</label>

            <label><input type="radio" name="<?php echo $v; ?>"
                          value="closed" <?php checked($typeofevents, 'closed'); ?> /> closed</label>
        </p>

        <?php
    }


    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['typeofevents'] = (!empty($new_instance['typeofevents'])) ? strip_tags($new_instance['typeofevents']) : '';
        $instance['numberofevents'] = (!empty($new_instance['numberofevents'])) ? strip_tags($new_instance['numberofevents']) : '';
        return $instance;
    }
}