<?php
    
    /**
     * Object sorting in descending and ascending mode 
     */
    function sort_array_object($data, $prop){
        $array = $data;
        $property = substr($prop, 0 , -4);

        if (strpos($prop, '_des') !== false) { 
            usort($array, function ($a, $b) use(&$property) {
                return (int) (strtolower($a->{$property}) < strtolower($b->{$property}));
            });
        }else{
            usort($array, function ($a, $b) use(&$property) {
                return (int) (strtolower($a->{$property}) > strtolower($b->{$property}));
            });
        }

        return $array;
    };

    /**
     * Check get data is available
     */
    function check_get_data($key){
        if(isset($_GET[$key])){
            $value = $_GET[$key];
            if($value != ''){
                return true;
            }
        }
        return false;
    };

    // import service
    require_once __ROOT__.'/services/wikipedia_service.php';

    // Get data from query
    $wikipedia_service = new WikipediaService();   
    $search = '';
    $results = [];

    if(check_get_data('fsearch')){
        
        $search = $_GET['fsearch'];
        
        $params = array(
            'srsearch' => $search,
        );

        $results = $wikipedia_service->get_results($params);
        
        if(check_get_data('sort_by')){
            $sort_by = $_GET['sort_by'];
            if($sort_by != 'relevance'){
                if($sort_by == 'views'){
                    $params['srqiprofile'] = 'popular_inclinks_pv';
                    $results = $wikipedia_service->get_results($params);
    
                }
                else{
                    $results = sort_array_object($results, $sort_by);
                }
            }
        }


    }

  
    // import view
    require_once __ROOT__.'/views/home.php'
?>
