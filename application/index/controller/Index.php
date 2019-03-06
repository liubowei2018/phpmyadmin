<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        echo "<h1 style='text-align: center'>Welcome to 荣点</h1>";
     }
     public function ceshi(){
         echo THINK_VERSION;
     }
}
