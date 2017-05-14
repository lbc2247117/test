<?php
/**
 * Created by liubocheng.
 * Time: 17-5-14 下午12:58
 *
 * 说明： rabbitmq的集群和高可用
 */

/**********************************************************************************************************************\
 * rabbitmq的节点有磁盘模式和内存模式
 * rabbitmq的集群有普通模式和镜像模式
 * 普通模式的集群是所有的节点都有消息队列的元数据，而队列本身还是存放在本地节点的，当从另一个节点a消费b节点的消息时，a节点会从b上获取消息，再从
 * a节点把消息传递给消费者，这种情况是当a或者b挂掉了,要从a或者b上获取b上的消息都会失败
 * 在rabbitmq集群中，至少有一个节点是磁盘模式
 * 在镜像模式中，消息会进行同步，比如消息是存在a上的，如果a挂掉了，通过节点b一样可以获取消息，只要在前端做一个负载均衡就行了
 * rabbitmq集群搭建步骤：
 * 这里是在一台物理机开三个端口来搭建的，分别为rabbit1=>5672,rabbit2=>5673,rabbit3=>5674
 * 0.配置erlang的集群
 * rabbitmq是用erlang写的，要实现rabbitmq的集群，就要先搭建erlang的集群
 * erlang的cookie保存在/var/lib/rabbitmq/.erlang.cookie中的，实现erlang的集群即所有节点的cookie相同
 * 把rabbit1的cookie复制到rabbit2和rabbit3中（目前演示的是同一台物理机，所以这个步骤就不需要做了）
 * 1.启动节点，执行如下命令
 * RABBITMQ_NODE_PORT=5672 RABBITMQ_NODENAME=rabbit1 rabbitmq-server -detached
 * RABBITMQ_NODE_PORT=5673 RABBITMQ_NODENAME=rabbit2 rabbitmq-server -detached
 * RABBITMQ_NODE_PORT=5674 RABBITMQ_NODENAME=rabbit3 rabbitmq-server -detached
 * 然后用netstat -lnt命令可以查看节点都启动了
 * 2.加入集群
 * 执行命令
 * rabbitmqctl -n rabbit2 stop_app
 * rabbitmqctl -n rabbit3 stop_app
 * 上面的命令是停止rabbit2和rabbit3的队列
 * 然后再执行命令
 * rabbitmqctl -n rabbit2 join_cluster --ram rabbit1@localhost
 * rabbitmqctl -n rabbit3 join_cluster --ram rabbit1@localhost
 * --ram表示节点为内存模式
 * 然后就可以用下面命令查看集群状态了
 * rabbitmqctl -n rabbit1 cluster_status
 * 此时，我们普通模式的集群就搭建好了
 * 3.高可用集群的搭建
 * 在普通模式下，我们搭建镜像模式，命令如下：
 * rabbitmqctl -n rabbit1 set_policy mypolicy "^" '{"ha-mode":"all"}' --priority 1
 * mypolicy表示名字，^表示所有的节点，ha-mode表示策略，all表示所有，priority表示优先级
 * 此时，rabbitmq的高可用就搭建好了
 * 只需要在rabbitmq前端搭建一个负载均衡就可以了，比如rabbit2节点挂掉了，负载均衡调度器就会连接rabbit3
 * \*******************************************************************************************************************/