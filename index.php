<?php
include "interpreter.php";

$ex1 = "
    fact := 1 ;
    val := 10000 ;
    cur := val ; mod := 1000000007 ;
    while ( cur > 1 )
        do {
            fact := fact * cur ;
            fact := fact - fact / mod * mod ;
            cur := cur - 1
        }
    cur := 0
";
$ex2 = "
    a := 10 ;
    b := 100 ;
    if ( a < b ) then
    { 
        min := a ; 
        max := b ;
    }
    else { 
        min := b ; 
        max := a ;
    }
";
$interpreter = new Interpreter();
echo "\r\n-------------------------------\r\n";
$interpreter
    ->loadSource($ex1)
    ->execute()
    ->output(false)
    ->close();

echo "\r\n-------------------------------\r\n";
$interpreter2 = new Interpreter();
$interpreter2
    ->loadSource($ex2)
    ->execute()
    ->output(false)
    ->close();
echo "\r\n-------------------------------\r\n";