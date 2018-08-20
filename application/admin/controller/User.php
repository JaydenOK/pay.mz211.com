<?php
namespace app\admin\controller;

use think\Db;

class User extends Auth
{
    public function userList()
    {
        $startTime = $this->getVal($this->rqData, 'start_time');
        $endTime = $this->getVal($this->rqData, 'end_time');
        $oauth = $this->getVal($this->rqData, 'oauth');
        $status = $this->getVal($this->rqData, 'status');
        $searchType = $this->getVal($this->rqData, 'search_type');
        $searchText = $this->getVal($this->rqData, 'search_text');
        $map = [];
        //支付时间
        if($startTime && $endTime) {
            $map['create_at'] = [['egt',strtotime($startTime)],['elt',strtotime($endTime)]];
        } else if($startTime) {
            $map['create_at'] = ['egt',strtotime($startTime)];
        } else if($endTime){
            $map['create_at'] = ['elt',strtotime($endTime)];
        }
        ($oauth !== '') && $map['oauth'] = $oauth;
        ($status !== '') && $map['status'] = $status;
        if($searchType !== ''){
            if($searchType == 1) {
                $map['mobile'] = $searchText;
            } else if($searchType == 2) {
                $map['user_id'] = $searchText;
            } else if($searchType == 3) {
                $map['nickname'] = ['like','%'.$searchText.'%'];
            }
        }
        $userList = Db::name('user')->where($map)->order(['create_at' => 'desc'])->paginate(20,false,['query'=>$this->rqData]);

        $this->assign('userList', $userList);
        return $this->fetch();
    }

}



/**
 * 状态说明 ：
 * 'ORDER_STATUS' => array(
 * 0 => '待确认',
 * 1 => '已确认',
 * 2 => '已收货',
 * 3 => '已取消',
 * 4 => '已完成',//评价完
 * 5 => '已作废',
 * ),
 * 'SHIPPING_STATUS' => array(
 * 0 => '未发货',
 * 1 => '已发货',
 * 2 => '部分发货'
 * ),
 * 'PAY_STATUS' => array(
 * 0 => '未支付',
 * 1 => '已支付',
 * ),
 * 'SEX' => array(
 * 0 => '保密',
 * 1 => '男',
 * 2 => '女'
 * ),
 * 'COUPON_TYPE' => array(
 * 0 => '面额模板',
 * 1 => '按用户发放',
 * 2 => '注册发放',
 * 3 => '邀请发放',
 * 4 => '线下发放'
 * ),
 * 'PROM_TYPE' => array(
 * 0 => '默认',
 * 1 => '抢购',
 * 2 => '团购',
 * 3 => '优惠'
 * ),
 * // 订单用户端显示状态
 * 'WAITPAY'=>' AND pay_status = 0 AND order_status = 0 AND pay_code !="cod" ', //订单查询状态 待支付
 * 'WAITSEND'=>' AND (pay_status=1 ｏｒ pay_code="cod") AND shipping_status !=1 AND order_status in(0,1) ', //订单查询状态 待发货
 * 'WAITRECEIVE'=>' AND shipping_status=1 AND order_status = 1 ', //订单查询状态 待收货
 * 'WAITCCOMMENT'=> ' AND order_status=2 ', // 待评价 确认收货     //'FINISHED'=>'  AND order_status=1 ', //订单查询状态 已完成
 * 'FINISH'=> ' AND order_status = 4 ', // 已完成
 * 'CANCEL'=> ' AND order_status = 3 ', // 已取消
 *
 * 'ORDER_STATUS_DESC' => array(
 * 'WAITPAY' => '待支付',
 * 'WAITSEND'=>'待发货',
 * 'WAITRECEIVE'=>'待收货',
 * 'WAITCCOMMENT'=> '待评价',
 * 'CANCEL'=> '已取消',
 * 'FINISH'=> '已完成', //
 * ),
 *
 *
 * 订单用户端显示按钮
 * 去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
 * 取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
 * 确认收货  AND shipping_status=1 AND order_status=0
 * 评价      AND order_status=1
 * 查看物流  if(!empty(物流单号))
 * 退货按钮（联系客服）  所有退换货操作， 都需要人工介入   不支持在线退换货
 */