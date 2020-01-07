<?php

use yii\db\Migration;

/**
 * Handles the creation of table `ExampleFeature`.
 */
class m170602_135615_create_examplefeature_table extends Migration
{
    public function up() {
			$this->createTable('ExampleFeature', [
				'exampleid' => $this->integer(),
				'featureid' => $this->integer(),
				'PRIMARY KEY(exampleid, featureid)',
			]);
			
			// creates index for column `exampleid`
			$this->createIndex(
				'idx-examplefeature-exampleid',
				'ExampleFeature',
				'exampleid'
			);
			
			// add foreign key for table `Example`
			$this->addForeignKey(
				'fk-examplefeature-exampleid',
				'ExampleFeature',
				'exampleid',
				'Example',
				'id',
				'CASCADE'
			);
			
			// creates index for column `featureid`
			$this->createIndex(
				'idx-examplefeature-featureid',
				'ExampleFeature',
				'featureid'
			);
			
			// add foreign key for table `Feature`
			$this->addForeignKey(
				'fk-examplefeature-featureid',
				'ExampleFeature',
				'featureid',
				'Feature',
				'id',
				'CASCADE'
			);
    }

    public function down() {
			// drops foreign key for table `Feature`
			$this->dropForeignKey(
				'fk-examplefeature-featureid',
				'ExampleFeature'
			);
			
			// drops index for column `featureid`
			$this->dropIndex(
				'idx-examplefeature-featureid',
				'ExampleFeature'
			);
			
			// drops foreign key for table `Example`
			$this->dropForeignKey(
				'fk-examplefeature-exampleid',
				'ExampleFeature'
			);
			
			// drops index for column `exampleid`
			$this->dropIndex(
				'idx-examplefeature-exampleid',
				'ExampleFeature'
			);
			
			$this->dropTable('ExampleFeature');
		}
}
