<?php
/**
 * Created by liubocheng.
 * Date: 17-5-14 下午11:23
 *
 * 介绍，redis是c语言写的
 */

/**
 * redis应用场景
 * 1.缓存
 * 2.队列
 * 3.数据存储
 */

/**
 * redis的安装
 * redis是C语言写的，所以要用GCC编译
 * 1.下载
 * 2.解压
 * 3.make
 * 4.make install
 */

/**
 * redis的配置
 * 通过lnmp安装的redis配置文件在/etc/redis/redis.conf中
 * 比较重要的参数有：
 * daemonize yes 表示redis在后台运行
 * port 6379 表示端口号是6379
 */

/**
 * redis的客户端redis-cli
 * 可以通过redis-cli来登录redis服务器
 * redis-cli -h localhost -p 6379
 * 如果不指定h表示在本机，如果不指定p表示端口为6379
 */

/**
 * redis的数据类型
 * 1.string：
 * 可以是字符串、整数、浮点数，统称为元素
 * 提供对字符串的操作和对整数的加减法操作命令
 * 2.list：
 * 一个序列集合且每个节点都包好了一个元素
 * 可对list进行pop、push操作，也可以进行查找、修改、删除操作
 * 3.set：
 * 是由多个唯一的元素组成的集合
 * 提供从集合中查找、插入或删除元素
 * 4.hash：
 * 是由多个key-value散列组组成的集合，其中key是字符串，value是元素，hash的key是唯一的
 * 按照key进行增删改查
 * 5.sort set：
 * 带分数的score-value有序集合，其中score为浮点，value为元素
 * 集合插入，按照分数范围查找
*/

/**
 * redis的string类型操作
 * set username liubocheng
 * get username --liubocheng
 * set age 30
 * get age  --30
 * incr age
 * get age  --31
 * decrby age 2
 * get age  --29
*/

/**
 * redis的list类型操作
 * lpush list1 12
 * lpush list1 13
 * lpush list1 13  --list中的值可以重复
 * rpop list1 --12 --l表示左边，r表示右边
 * llen list1 --2 获取list的长度
*/

/**
 * redis的set类型操作
 * sadd set1 12 --给set中添加元素
 * scard set1 --获取set中的元素个数
 * sadd set1 13
 * sadd set1 13 --由于set是无序去重的类型，所以这条命令不会再多一条数据
 * sismember set1 13 --判断元素是否在set中，1表示在，0表示没在
 * srem set1 13 --删除元素
*/

/**
 * redis的hash类型操作
 * hset hash1 key1 12
 * hget hash1 key1  --12
 * hset hash1 key2 13
 * hset hash1 key3 13
 * hlen hash1 --3
 * hset hash1 key3 14
 * hget hash1 key3 --14
 * 和操作string类型一样，只不过hash类型多了一层
*/

/**
 * redis的sort set类型操作
 * sort set其实就是一个有序的list
 * zadd zset1 10.1 val1
 * zadd zset1 11.2 val2
 * zadd zset1 9.2 val3
 * zcard zset1 --3
 * zrange zset1 0 2 withscores --输出排名0到2的数据
 * zrank zset1 val2 --2 --输出zset1中val2的排名
 * zadd zset1 11.2 val3  --修改val3的值为11.2,
 * 此时val2和val3的值都是11.2，在值相同的情况下，排名就是按key的顺序来排的，val2和val3中val都相同，2小于3，所以val2排在前面
*/