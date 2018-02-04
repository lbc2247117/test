<?php

class API
{
    /**
     * the doc info will be generated automatically into service info page.
     * @params
     * @return
     */
    public function testapi($parameter, $option = "foo")
    {
        $result = [];
        if ($result['data'])
            return ['code' => 200, 'msg' => '成功', 'data' => []];
        else
            return ['code' => 300, 'msg' => '失败', 'data' => []];
    }

    protected function client_can_not_see()
    {
    }
}

error_reporting(E_WARNING);
$service = new Yar_Server(new API());
$service->handle();