<?php

class MenuMaster {
    private $file;
    //food matrix used to hold prices of the user's food choice at restaurant
    private $foodItems;
    // helper array to populate and iterate through food matrix
    private $restaurants = array();
    private $choices = array();
    
    function MenuMaster(&$argv){
        $this->file = $argv[1];
        for ($i = 2; $i < sizeof($argv); $i++) {
            $string = $argv[$i];
            $this->choices[] = $string;
        }
    }
    public function readline() {
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
                $key = trim($data[2]);
                
                if(in_array($key, $this->choices)){
                   $restuarant = (int)trim($data[0]);
                   $price = (float)(trim($data[1]));
                   $this->foodItems[$restuarant][$key] = $price;
                   $this->restaurants[] = $restuarant;
                }
                
              
            }
            fclose($handle);
        }
        //print_r($this->foodItems);
       
    }

    function calculatePrices(){
        //Food matrix is empty, no need to continue
        if(!isset($this->foodItems)){
            echo("NIL\n");
            return;
        }
        $totalRestaurant= NULL; 
        $total=NULL;
        foreach($this->restaurants as $restaurant) {
            // don't consider restaurant 
            $count = count($this->foodItems[$restaurant]);
            if(($count < count($this->choices))){
               continue;
            }
            $subTotal=0.0;
            foreach($this->choices as $choice){
                $subTotal += $this->foodItems[$restaurant][$choice];
            }
            if($total==NULL || $subTotal < $total){
                $total = $subTotal;
                $totalRestaurant = $restaurant;
            }
        }
        
        if($total==NULL){
            echo("NIL\n");
            return;
        }
        echo("Winning Restaurant: $totalRestaurant and winning Price: $total\n");
    }
    
}
if(isset($argv)&& count($argv)>=3){
    $menuMaster = new MenuMaster($argv);
    $menuMaster->readline();
    $menuMaster->calculatePrices();
}
?>
