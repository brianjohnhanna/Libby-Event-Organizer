SELECT post_id
FROM
  ( SELECT wp_eo_events.post_id AS post_id,
           CASE
               WHEN MAX( CASE
                             WHEN wp_postmeta.meta_key = '_libby_setup_time' THEN wp_postmeta.meta_value
                             ELSE NULL
                         END )REGEXP '^[0-9]+$' THEN DATE_SUB(wp_eo_events.StartTime, INTERVAL MAX( CASE
                                                                                                        WHEN wp_postmeta.meta_key = '_libby_setup_time' THEN wp_postmeta.meta_value
                                                                                                        ELSE NULL
                                                                                                    END ) MINUTE)
               ELSE wp_eo_events.StartTime
           END AS start_time,
           CASE
               WHEN ( MAX( CASE
                               WHEN wp_postmeta.meta_key = '_libby_breakdown_time' THEN wp_postmeta.meta_value
                               ELSE NULL
                           END ) ) REGEXP '^[0-9]+$' THEN DATE_ADD(wp_eo_events.FinishTime, INTERVAL MAX( CASE
                                                                                                              WHEN wp_postmeta.meta_key = '_libby_breakdown_time' THEN wp_postmeta.meta_value
                                                                                                              ELSE NULL
                                                                                                          END ) MINUTE)
               ELSE wp_eo_events.FinishTime
           END AS finish_time,

           
      wp_eo_events.EndDate,
      wp_eo_events.StartDate,
      wp_term_taxonomy.term_id,
      wp_term_taxonomy.taxonomy

    FROM wp_eo_events
    LEFT JOIN wp_term_relationships ON( wp_eo_events.post_id = wp_term_relationships.object_id )
    LEFT JOIN wp_term_taxonomy ON( wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id )
    JOIN wp_postmeta 
    WHERE 
    taxonomy = 'event-venue'
    AND term_id = 3
    AND wp_eo_events.post_id <> 698

   GROUP BY post_id,
            StartTime,
            FinishTime ) 
AS temp_table
WHERE ( ( EndDate IN('2018-04-19') AND( finish_time BETWEEN '18:30:01' AND '19:30:01' ) )
       OR ( StartDate IN('2018-04-19') AND( start_time BETWEEN '12:29:59' AND '13:29:59' ) )
       OR ( StartDate IN('2018-04-10')
           AND EndDate IN('2018-04-19') AND( ( start_time < '12:30:00'
                                              AND finish_time > '13:30:00' ) OR( start_time > '12:30:00'
                                                                                AND finish_time < '13:30:00' ) ) ) )
  