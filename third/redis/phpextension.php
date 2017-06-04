<?php
/**
 * php操作redis
 *
 * Created by liubocheng.
 * Time: 17-5-22 下午8:32
 */

$redis = new Redis();
$redis->connect('127.0.0.1', 6379); //连接

//php的redis中的string操作
$redis->set('username', '刘波成'); //设置
$username = $redis->get('username'); //读取
$redis->delete('username'); //删除
$redis->set('age', 29);
$redis->incr('age'); //自增1
$redis->incrBy('age', 2); //自定义自增
$redis->decr('age'); //自减1
$redis->decrBy('age', 2);//自定义自减
$age = $redis->get('age');

//php的redis中的list操作
$redis->lPush('list', '刘波成'); //从左边推入
$redis->lPush('list', 29); //从左边推入
$len = $redis->lLen('list'); //获取队列的长度
$key = $redis->rPop('list'); //从右边推出，刘波成
$redis->delete('list'); //删除list

//php的redis中的set操作
$redis->sAdd('set', 'a'); //添加元素
$redis->sAdd('set', 'b');
$redis->sAdd('set', 'c');
$redis->sAdd('set', 'c');
$redis->sRem('set', 'a');//删除元素
$len = $redis->sCard('set');//获取长度
$redis->delete('set'); //删除

//php的redis中的hash操作
$redis->hSet('hash1', 'username', '刘波成');
$redis->hSet('hash1', 'age', 29);
$age = $redis->hGet('hash1', 'age');
$arr = $redis->hMGet('hash1', ['username', 'age']); //获取多个值，参数是数组，返回值也是数组

//php的redis中的zset类型操作
$redis->delete('zset1');
$redis->zAdd('zset1', 100, 'r');
$redis->zAdd('zset1', 90, 'g');
$redis->zAdd('zset1', 80, 'b');
$arr = $redis->zRange('zset1', 0, -1); //从小到大排序
$arr = $redis->zRevRange('zset1', 0, -1); //从大到小排序


//过期时间设置
$redis->set('username', '刘波成');
$redis->expire('username', 900); //设置过期时间为900秒，成功返回1，失败或者key不存在返回0
$time = $redis->ttl('username'); //查询key还有多久过期，没有过期时间范湖-1，key不存在返回-2
$redis->persist('username'); //把一个key设置为无过期时间


/*redis缓存策略
 *
 * 当服务器内存有限时,如果大量地使用缓存键且过期时间设置得过长就会导致 Redis 占满内存；
 * 而过期时间设置得太小，则会容易出现缓存命中率低；
 * 实际开发中，很难对缓存的过期时间进行合理的设置；
 * 所以我们可以给redis设置一定的内存大小，内存用满时则按照一定的规则淘汰不需要的缓存
 * 1.修改配置文件maxmemory的大小
 * 2.修改maxmemory-policy参数，这个参数是删除的策略，LRU算法是“最近最少使用”
 * maxmemory-policy有以下几个参数：
 * a.allkeys-lru        --所有的键采用LRU算法删除一个键
 * b.volatile-lru       --设置了缓存时间的键采用LRU算法删除一个键
 * c.noeviction         --只报错，不删除
 * d.allkeys-random     --所有的键随机删除一个键
 * e.volatile-random    --设置了缓存时间的键随机删除一个键
*/

/**
 * redis缓存穿透问题
 *
 * 缓存穿透是指通过key查询的数据一定不存在，然后业务逻辑就会请求后端数据库，如果并发量稍大，就会对后端数据库的压力就会很大
 *
 * 解决方案：
 * 1.对不存在的key，如果请求数据库也不存在，则设置一个null值，而这个空值的过期时间很短，不会超过5分钟
 * 2.把可能会存在的key存放在一个bitmap中，查询缓存时，先在bitmap中过滤下（个人不是很推荐）
*/

/**
 * redis缓存并发问题
*/