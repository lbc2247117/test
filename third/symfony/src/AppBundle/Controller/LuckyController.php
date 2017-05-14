<?php
// src/AppBundle/Controller/LuckyController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LuckyController extends Controller
{
    /**
     * symfony的路由可以写在注释里面，如下，这里是定义了一个路由规则，匹配/lucky/page
     * 因为这里有requirements来匹配，所以这里只接收数字
     * 因为这里给$page定义了一个默认值，所以当我们不带page的时候，也会默认跳转到这里，$page默认是1
     *
     * @Route("/lucky/{page}", name="lucky_list", requirements={"page": "\d+"})
     */
    public function numberAction($page = 1)
    {
        $number = mt_rand(0, 100);

        return $this->render('lucky/number.html.twig', array(
            'number' => $number,
        ));
    }
    /**
     * @Route("/lucky/{slug}", name="lucky_show")
     */
    public function paramAction($slug)
    {
        exit($slug);
    }

}