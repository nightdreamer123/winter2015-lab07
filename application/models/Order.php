<?php

class Order extends CI_Model {

    protected $xml = null;
    public $customer;
    public $type;
    public $orderInstructions = "";
    public $burgers = array();
    public $total = 0.00;

    // Constructor
    public function __construct($filename = null) 
    {
        parent::__construct();
        if ($filename == null)
        {
            return;
        }
        
        $this->load->model('menu');
        
        $this->xml = simplexml_load_file(DATAPATH . $filename);

        // name of the customer
        $this->customer = (string) $this->xml->customer;
        
        // order type -> eat in, to go, etc
        $this->type = (string) $this->xml['type'];
        
        // special instructions
        if (isset($this->xml->special))
        {
            $this->orderInstructions = (string) $this->xml->special;
        }
        
        $i = 0;
        foreach ($this->xml->burger as $burger)
        {
            $i++;
            
            $newBurger = array(
                'patty' => $burger->patty['type']
            );
            
            
            // give the burger a number
            $newBurger['num'] = $i;
            $cheeses = "";
            if (isset($burger->cheeses['top']))
            {
                $cheeses .= $burger->cheeses['top'] . "(top), ";
            }
            
            if (isset($burger->cheeses['bottom']))
            {
                $cheeses .= $burger->cheeses['bottom'] . "(bottom)";
            }
            
            $newBurger['cheese'] = $cheeses;
            
            $toppings = "";
            // no topping?
            if (!isset($burger->topping))
            {
                $toppings .= "none";    
            }
            
            foreach($burger->topping as $topping)
            {
                $toppings .= $topping['type'] . ", ";
            }
            
            $newBurger['toppings'] = $toppings;
            
            $sauces = "";
            // If we have no sauces
            if (!isset($burger->sauce))
            {
                $sauces .= "none";    
            }
            
            foreach($burger->sauce as $sauce)
            {
                $sauces .= $sauce['type'] . ", ";
            }
            
            $newBurger['sauces'] = $sauces;
            // the special instructions
            if (isset($burger->instructions))
            {
                $newBurger['instructions'] = (string) $burger->instructions;
            }
            else
            {
                $newBurger['instructions'] = "";
            }
            
            // the price (cost)
            $cost = $this->getBurgerCost($burger);
            
            $newBurger['cost'] = $cost;
            $this->total += $cost;
                        
            // add burger to the list
            $this->burgers[] = $newBurger;
        }
    }
    
    private function getBurgerCost($burger)
    {
        $burgerTotal = 0.00;
        
        // Add patty to total price of burger
        $burgerTotal += $this->menu->getPatty((string) $burger->patty['type'])->price;
        
        // Add cheese to total price of burger ->top
        if (isset($burger->cheeses['top']))
        {
            $burgerTotal += $this->menu->getCheese((string) $burger->cheeses['top'])->price; 
        }
        //bottom one
        if (isset($burger->cheeses['bottom']))
        {
            $burgerTotal += $this->menu->getCheese((string) $burger->cheeses['bottom'])->price; 
        }
        
        // Add topping prices to total of burger
        foreach ($burger->topping as $topping)
        {
            $burgerTotal += $this->menu->getTopping((string) $topping['type'])->price; 
        }
        
        // Add sauce price to the total of the burger
        foreach ($burger->sauce as $sauce)
        {
            $burgerTotal += $this->menu->getSauce((string) $sauce['type'])->price; 
        }
        return $burgerTotal;
    }   
}
