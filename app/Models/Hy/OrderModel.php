<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Models\Hy;

class OrderModel extends HyModel
{
    const ORDER_CREATE = 10;
    const ORDER_CANCEL = 20;
    const ORDER_SUCCESS= 30;
    const ORDER_FAIL   = 40;
    const ORDER_REFUND = 50;
    protected $table = 'hy_order';
}
