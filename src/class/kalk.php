<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Kalk
{
    protected $val1 = 0;
    
    function __construct()
    {
        
    }
    
    public function dodawanie($liczba1, $liczba2)
    {
        return  $this->val1=  $liczba1 + $liczba2;
    }
}