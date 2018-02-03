<?php
namespace Wangjian\MQServer\Lib;

class BinaryTreeNode
{
    public $value;

    public $left;

    public $right;

    public function __construct($value, $left = null, $right = null)
    {
        $this->value = $value;
        $this->left = $left;
        $this->right = $right;
    }
}
