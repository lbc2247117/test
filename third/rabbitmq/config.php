<?php
/**
 * Created by liubocheng.
 * this is a note for rabbitmq config
 * Date: 17-5-13
 * Time: 下午5:05
 */

/**
 * rabbitmq是有默认配置的，一般情况下是不需要配置
 * rabbitmq也提供了一些配置让我们来自定义
 * 我们主要可以定义rabbitmq.config和rabbitmq-env.conf
 * 在linux系统中，rabbitmq会先去找/etc/rabbitmq/rabbitmq-env.conf，如果这个文件不存在，则会使用默认配置
 * 如果rabbitmq-env.conf存在，则使用rabbitmq-env.conf的配置
 *
 * 在rabbitmq-env.conf中可以定义rabbitmq.config的配置，即通过CONFIG_FILE这次参数定义
 * 如果没有定义CONFIG_FILE或者定义的文件找不到，rabbitmq就会采用默认的配置
 * 如果找到了，就使用rabbitmq.config的配置文件
 *
 * 注意：
 * 在CONFIG_FILE这个参数中，是要省略.config的，比如我们要定义rabbitmq.config的位置为/etc/rabbitmq/rabbitmq.config,我们直接如下写：
 * CONFIG_FILE=/etc/rabbitmq/rabbitmq
*/


/**
 * 检查配置文件位置
 *
 * 在我们用rabbitmq-service启动时，会告诉我们日志的位置
 * 我们可以去查看日志
 *
 * node           : rabbit@example
 * home dir       : /var/lib/rabbitmq
 * config file(s) : /etc/rabbitmq/rabbitmq.config
 *
 * 如果配置文件没找到，则会在后面显示not found
 * config file(s) : /etc/rabbitmq/rabbitmq.config(not found)
*/