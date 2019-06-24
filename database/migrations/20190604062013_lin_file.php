<?php

use think\migration\Migrator;
use think\migration\db\Column;

class LinFile extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('lin_file', array('engine' => 'InnoDB'));
        $table->addColumn('path', 'string', array('limit' => 500,'comment'=>'路径'))
            ->addColumn('type', 'integer', array('limit' => 6,'comment'=>'1 local，其他表示其他地方'))
            ->addColumn('name', 'string', array('limit' => 100,'comment'=>'名称'))
            ->addColumn('extension', 'string', array('limit' => 50,'comment'=>'后缀'))
            ->addColumn('size', 'integer', array('limit' => 11,'comment'=>'大小'))
            ->addColumn('md5', 'string', array('limit' => 40,'comment'=>'图片md5值，防止上传重复图片'))
            ->addColumn('create_time', 'datetime', array('null' => 'null'))
            ->addColumn('update_time', 'datetime', array('null' => 'null'))
            ->addColumn('delete_time', 'datetime', array('null' => 'null'))
            ->addIndex(array('md5'))
            ->create();

    }
}
