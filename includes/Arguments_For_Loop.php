<?php

class Arguments_For_Loop
{


    public static function arguments(string $numOfEvents, string $typeofEvents): array
    {


        $dateNow = date_create('now');
        $dateNow = date_format($dateNow, "Y-m-d");

        $args2 = array(
            'post_type' => 'events',
            'posts_per_page' => $numOfEvents,
            'meta_key' => 'eventdate',
            'meta_query' => array(
                array(
                    'key' => 'status',
                    'value' => $typeofEvents,//ищу  события по статусу
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


       return $args2;

    }



}