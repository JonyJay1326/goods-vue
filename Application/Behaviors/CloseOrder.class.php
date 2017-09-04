<?php
namespace Application\Behaviors;

class CloseOrder extends \Think\Behavior
{
    public function run()
    {
        echo 'a';exit;
    }
}