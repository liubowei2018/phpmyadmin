<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/16
 * Time: 15:54
 */

namespace app\admins\controller;


class Member extends Base
{
    public function index(){

        return $this->fetch();
    }
}