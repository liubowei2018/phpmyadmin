<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        echo "<h1 style='text-align: center'>Hello World</h1>";
     }
     public function ceshi(){
         echo THINK_VERSION;
     }
}
