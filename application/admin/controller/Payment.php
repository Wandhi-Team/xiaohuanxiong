<?php


namespace app\admin\controller;


use app\model\User;
use app\model\UserFinance;
use app\service\FinanceService;
use think\facade\App;

class Payment extends BaseAdmin
{
    protected $financeService;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->financeService = app('financeService');
    }

    //支付配置文件
    public function index()
    {
        if ($this->request->isPost()) {
            $content = input('json');
            file_put_contents(App::getRootPath() . 'config/payment.php', $content);
            $this->success('保存成功');
        }
        $content = file_get_contents(App::getRootPath() . 'config/payment.php');
        $this->assign('json', $content);
        return view();
    }

    //订单查询
    public function orders()
    {
        $id = input('id');
        $uid = input('uid');
        $status = (int)input('status');
        $map = array();
        if ($id) {
            $map[] = ['id', '=', $id];
        }
        if ($uid) {
            $map[] = ['user_id', '=', $uid];
        }

        if ($status) {
            $map[] = ['status', '=', $status == 2 ? 0 : 1];
        }

        $data = $this->financeService->getPagedOrders($map);
        $this->assign([
            'orders' => $data['orders'],
            'count' => $data['count']
        ]);
        return view();
    }

    //用户消费记录
    public function finance()
    {
        $uid = input('uid');
        $usage = input('usage');
        $map = array();
        if ($uid) {
            $map[] = ['user_id', '=', $uid];
        }
        if ($usage) {
            $map[] = ['usage', '=', $usage];
        }
        $data = $this->financeService->getPagedFinance($map);
        $this->assign([
            'finances' => $data['finances'],
            'count' => $data['count']
        ]);
        return view();
    }

    //用户购买记录
    public function buy()
    {
        $data = $this->financeService->getPagedBuyHistory();
        $this->assign([ 
            'buys' => $data['buys'],
            'count' => $data['count']
        ]);
        return view();
    }

    public function charge()
    {
        if ($this->request->isPost()) {
            $uid = input('uid');
            $user = User::get($uid);
            if (!$user) {
                $this->error('用户不存在');
            }
            $money = input('money');
            $userFinance = new UserFinance();
            $userFinance->user_id = $uid;
            $userFinance->money = $money;
            $userFinance->usage = 1;
            $userFinance->summary = '代充值';
            $userFinance->save();
            $this->success('充值成功');
        }
        return view();
    }
}