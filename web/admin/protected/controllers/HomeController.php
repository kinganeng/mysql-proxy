<?php

class HomeController extends Controller
{

    /**
     * 首页.
     */
    public function actionIndex()
    {
        $redis = new redis();
        $redis->connect("127.0.0.1", 6379);
//        $list = $redis->lrange("sqllist".$date, 0, 10000);
        $date = date("Y-m-d");
        $ret = [
            'slowTop' => [],
            'bigTop'  => []
        ];
        $slowArr = $redis->zRevRange("sqlslow" . $date, 0, 100);//汇总所有的proxy
        foreach ($slowArr as $k => $ser)
        {
            $ret['slowTop'][] = swoole_serialize::unpack($ser);
        }
        $bigArr = $redis->zRevRange("sqlbig" . $date, 0, 100);//汇总所有的proxy
        foreach ($bigArr as $k => $ser)
        {
            $ret['bigTop'][] = swoole_serialize::unpack($ser);
        }


        $conns = $redis->hGetAll("proxy_connection");//proxy维度 不汇总
        $tarArr = array();
        foreach ($conns as $proxyip => $ser)
        {
            $tarArr[$proxyip] = swoole_serialize::unpack($ser);
        }

//        foreach ($list as $value)
//        {
//            $data[] = swoole_serialize::unpack($value);
//        }
        $ret['qps'] = (int)$redis->get("proxy_qps");
        $ret['connCount'] = $tarArr;
        $this->render('/home/index', $ret);
    }

}
