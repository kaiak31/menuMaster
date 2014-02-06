<?php

class MenuMaster {

    private $file;
    //food matrix used to hold prices of the user's food choice at restaurant
    private $foodItems;
    // helper array to populate and iterate through food matrix
    private $restaurants = array();
    private $choices = array();
    private $combos = array();

    function MenuMaster(&$argv) {
        $this->file = $argv[1];
        
        for ($i = 2; $i < sizeof($argv); $i++) {
            $string = $argv[$i];
            $this->choices[] = $string;
        }
        
    }

    public function addRestaurantRow($restaurant) {
        if (!isset($this->foodItems[$restaurant])) {
            foreach ($this->choices as $choice) {
                $this->foodItems[$restaurant][$choice] = null;
            }
            $this->restaurants[] = $restaurant;
        }
    }

    public function readline() {
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
                $restaurant = (int) trim($data[0]);
                $this->addRestaurantRow($restaurant);

                //save combos for later processing
                if (count($data) > 3) {
                    $this->saveCombos($data);
                    continue;
                }

                //TODO: Some wierd warning 
                if (!isset($data[2])) {
                    continue;
                }

                $key = trim($data[2]);
                /* this line has a foodItem the user wants, 
                 * We will store in the food matrix
                 */
                if (in_array($key, $this->choices)) {
                    $price = (float) (trim($data[1]));
                    /*
                      Since we have no control of the order of the incomgin menu items we want to
                      choose the price of the cheaper option in the event there is a combo.
                      usually the will be a solo offering rather than a combo.

                      if(isset($this->foodItems[$restuarant][$key])){
                      $oldPrice =  $this->foodItems[$restuarant][$key];
                      $price = ($oldPrice<=$price)?$oldPrice:$price;
                      } */

                    $this->foodItems[$restaurant][$key] = $price;
                }
            }
            fclose($handle);
        }
    }

    function saveCombos(& $data) {
        $restaurant = trim($data[0]);
        $price = trim($data[1]);
        $comboKeys = array_slice($data, 2);
        $comboEntry = array(
            'price'=>$price,
            'keys'=>array()    
         );
       
        //I hate whitespaces
        foreach ($comboKeys as $key) {
            $key = trim($key);
            if ((in_array($key, $this->choices))) {
                $comboEntry['keys'][] = $key;
                //$this->combos[$restaurant][]['keys'][] = $key;
            }
        }
        if(count($comboEntry['keys'] > 0)){
            $this->combos[$restaurant][]=$comboEntry;
        }
    }
    
    function smash(&$matrix, &$combo){
        foreach($combo['keys'] as $foodItem){
            $matrix[$foodItem] = 0;
        }
        $matrix[$combo['keys'][0]] = $combo['price'];
    }

    function addCombo($restaurant, &$combo) {
        //check to see if combo has all the choices or don't bother adding
        $matrix = & $this->foodItems[$restaurant];
        $subTotal = 0.0;
        foreach ($combo['keys'] as $comboItem) {
            if (isset($matrix[$comboItem])) {
                $subTotal += $matrix[$comboItem];
            }else{
                $this->smash($matrix, $combo);
                return;
            }
        }
        if($subTotal> $combo['price']){
            $this->smash($matrix, $combo);
        }
    }

    /**
     * We now want to normalize the food matrix with the combos
     * we take each combs and determine whether they are a better deal 
     * than what's already there
     */
    function processCombos() {
        $restaurants = array_keys($this->combos);
        foreach ($restaurants as $restaurant) {
            if (!isset($this->foodItems[$restaurant])) {
                //adding new restuarant
                addRestaurantRow($restaurant);
            }
            foreach ($this->combos[$restaurant] as $combo) {
                $this->addCombo($restaurant, $combo);
            }
        }
    }

    function calculatePrices() {
        //Food matrix is empty, no need to continue
        if (!isset($this->foodItems)) {
            echo("NIL\n");
            return;
        }
        //print_r($this->foodItems);
        $totalRestaurant = NULL;
        $total = NULL;
        foreach ($this->restaurants as $restaurant) {
            // don't consider restaurant 
            $subTotal = 0.0;
            foreach ($this->choices as $choice) {
                if($this->foodItems[$restaurant][$choice] !== NULL){
                    $subTotal += $this->foodItems[$restaurant][$choice];
                }else{
                    continue 2;
                }
            }
            if ($total == NULL || $subTotal < $total) {
                $total = $subTotal;
                $totalRestaurant = $restaurant;
            }
        }

        if ($total == NULL) {
            echo("NIL\n");
            return;
        }
        $total = money_format($total, 3);
        echo("Winning Restaurant: $totalRestaurant and winning Price: $total");
    }

}

if (isset($argv) && count($argv) >= 3) {
    $menuMaster = new MenuMaster($argv);
    $menuMaster->readline();
    $menuMaster->processCombos();
    $menuMaster->calculatePrices();
}else{
   echo("NIL\n");
   exit(1);

}
?>
