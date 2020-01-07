<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Example`.
 */
class m170602_134149_create_example_table extends Migration
{
    public function up() {
			// construct the `Example` table
			$this->createTable('Example', [
				'id' => $this->primaryKey(),
				'backendid' => $this->integer()->notNull(),
				'languageid' => $this->integer()->notNull(),
				'publicationid' => $this->integer(),
				'title' => $this->string()->notNull(),
				'link' => $this->string()->notNull(),
				'description' => $this->text()->notNull(),
				'date' => $this->date()->notNull(),
			]);
			
			// creates index for column `backendid`
			$this->createIndex(
				'idx-example-backendid',
				'Example',
				'backendid'
			);
			
			// add foreign key for table `Backend`
			$this->addForeignKey(
				'fk-example-backendid',
				'Example',
				'backendid',
				'Backend',
				'id',
				'CASCADE'
			);
			
			// creates index for column `languageid`
			$this->createIndex(
				'idx-example-languageid',
				'Example',
				'languageid'
			);
			
			// add foreign key for table `Language`
			$this->addForeignKey(
				'fk-example-languageid',
				'Example',
				'languageid',
				'Language',
				'id',
				'CASCADE'
			);
			
			// creates index for column `publicationid`
			$this->createIndex(
				'idx-example-publicationid',
				'Example',
				'publicationid'
			);
			
			// add foreign key for table `Publication`
			$this->addForeignKey(
				'fk-example-publicationid',
				'Example',
				'publicationid',
				'Publication',
				'id',
				'CASCADE'
			);
    }

    public function down() {
			// drops foreign key for table `Publication`
			$this->dropForeignKey(
				'fk-example-publicationid',
				'Example'
			);
			
			// drops index for column `publicationid`
			$this->dropIndex(
				'idx-example-publicationid',
				'Example'
			);
			
			// drops foreign key for table `Language`
			$this->dropForeignKey(
				'fk-example-languageid',
				'Example'
			);
			
			// drops index for column `languageid`
			$this->dropIndex(
				'idx-example-languageid',
				'Example'
			);
			
			// drops foreign key for table `Backend`
			$this->dropForeignKey(
				'fk-example-backendid',
				'Example'
			);
			
			// drops index for column `backendid`
			$this->dropIndex(
				'idx-example-backendid',
				'Example'
			);
			
			$this->dropTable('Example');
    }
}
