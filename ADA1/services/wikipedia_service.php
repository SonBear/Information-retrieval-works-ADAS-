<?php
    /**
     * Service that consume wikipedia api
     */
    class WikipediaService{

        private  $URL_API = 'https://en.wikipedia.org/w/api.php';
        private  $URL_WIKI_SEARCH = 'https://en.wikipedia.org/?curid=';

        /**
         * Builder param string from object
         */
        private function create_query_params($params){
            

            $array = get_object_vars($params);
            $properties = array_keys($array);
            
            $url_params = '?';
            if(sizeof($properties) <= 0){
                return '';
            }
            
            foreach($properties as $property){
                $enconde_prop = urlencode($params->{$property});
                $url_params = $url_params.$property.'='.$enconde_prop.'&';
            }
        
            return substr($url_params, 0 , -1);
        }

        /**
         * Get results from params
         */
        public function get_results($params){
            // Crear un flujo
            $opciones = array(
                'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
                )
            );
          
            $default_params = array(
                'action' => 'query',
                'list' => 'search',
                'format' => 'json',
            );

            $url_params = $this->create_query_params((object)array_merge($default_params, $params));
            
            $contexto = stream_context_create($opciones);
            
            // Abre el fichero usando las cabeceras HTTP establecidas arriba
            $url = $this->URL_API.$url_params;

            $fichero = file_get_contents($url, false, $contexto);
            
            $response = json_decode($fichero);

            $data = $response->query->search;
            foreach ($data as $search) {
                $search-> url = $this->URL_WIKI_SEARCH.$search->pageid;
            }
            return $response->query->search;
        } 



    }


    
    

?>