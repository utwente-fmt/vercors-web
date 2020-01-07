<?php

use yii\db\Migration;

class m180228_120905_add_source_column extends Migration
{
    public function up()
    {
	$this->createTable('Source', [
		'id' => $this->primaryKey(),
		'name' => $this->string()->notNull(),
	]);
	
        $this->createTable('ExampleSource', [
			'exampleid' => $this->integer(),
			'sourceid' => $this->integer(),
			'PRIMARY KEY(exampleid, sourceid)',
		]);
		
	// creates index for column `exampleid`
	$this->createIndex(
			'idx-examplesource-exampleid',
			'ExampleSource',
			'exampleid'
		);
		
	// add foreign key for table `Example`
	$this->addForeignKey(
			'fk-examplesource-exampleid',
			'ExampleSource',
			'exampleid',
			'Example',
			'id',
			'CASCADE'
		);
		
	// creates index for column `sourceid`
	$this->createIndex(
			'idx-examplesource-sourceid',
			'ExampleSource',
			'sourceid'
		);
		
	// add foreign key for table `Source`
	$this->addForeignKey(
			'fk-examplesource-sourceid',
			'ExampleSource',
			'sourceid',
			'Source',
			'id',
			'CASCADE'
		);
    }

    public function down()
    {
	$this->dropTable('ExampleSource');
	$this->dropTable('Source');	
    }
}
